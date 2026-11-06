<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Item;
use App\Services\InventorySyncService;
use App\Services\SkuGenerator;
use App\Models\StorageLocation;
use App\Models\ActivityLog;
use App\Models\StockBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $availableStorageLocations = StorageLocation::availableInMambaling()->orderBy('warehouse_name')->orderBy('name')->get();
        $storageLocations = $availableStorageLocations;
        $selectedLocationId = $request->query('location');
        $selectedLocation = $selectedLocationId !== null
            ? $storageLocations->firstWhere('id', (int) $selectedLocationId)
            : null;
        abort_if($selectedLocationId !== null && ! $selectedLocation, 404);

        $allItems = Item::with('stockBatches')
            ->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);

        if ($selectedLocation) {
            $allItems = $allItems->filter(function (Item $item) use ($selectedLocation): bool {
                $allBatches = $item->stockBatches;
                $locationBatches = $allBatches->filter(fn (StockBatch $batch): bool =>
                    $batch->storage_location === $selectedLocation->name
                );
                $item->setRelation('stockBatches', $locationBatches->values());

                return $allBatches->isEmpty()
                    ? $item->storage_location === $selectedLocation->name
                    : $locationBatches->isNotEmpty();
            });
        }

        $items = $allItems
            ->groupBy('category')
            ->sortKeys();

        // Same stock calculation and low-stock threshold (10 units) used on
        // the Reports page, so the numbers always match across the system.
        $stockFor = fn (Item $item): int => (int) $item->stockBatches->sum('quantity');

        // Same 30-day "expiring soon" window used on the Reports page.
        // "expired" (already past date) and "expiring" (within 30 days, not
        // yet past) are kept mutually exclusive so they never double-count
        // the same item.
        $expired = $allItems->filter(function (Item $item): bool {
            return $item->next_expiration_date
                && \Carbon\Carbon::parse($item->next_expiration_date)->isPast();
        })->count();

        $expiring = $allItems->filter(function (Item $item): bool {
            return $item->next_expiration_date
                && \Carbon\Carbon::parse($item->next_expiration_date)->isFuture()
                && \Carbon\Carbon::parse($item->next_expiration_date)->lte(now()->addDays(30));
        })->count();

        $inventoryStats = [
            'totalItems' => $allItems->count(),
            'totalStock' => $allItems->sum(fn (Item $item): int => $stockFor($item)),
            'lowStock'   => $allItems->filter(fn (Item $item): bool => $stockFor($item) > 0 && $stockFor($item) < ($item->min_stock ?? 10))->count(),
            'outOfStock' => $allItems->filter(fn (Item $item): bool => $stockFor($item) <= 0)->count(),
            'expired'    => $expired,
            'expiring'   => $expiring,
        ];

        return view('inventory.index', compact('items', 'inventoryStats', 'storageLocations', 'availableStorageLocations', 'selectedLocation', 'selectedLocationId'));
    }

    
public function create()
{
    $defaultCategories = ['FOOD', 'MEDICAL', 'RESCUE', 'RELIEF'];
    $defaultUnits = ['BOX', 'PACK', 'PCS', 'SACK', 'BOTTLE'];
    $defaultSizes = ['SM', 'MD', 'LG', '50KG', '25KG', '500ML', '1L', 'REG'];
    $defaultTargets = ['ADULT', 'CHILD', 'ALL'];
    $defaultVariants = ['NONE', 'REG', 'SPICY', 'SWEET'];

    // Get every distinct category already saved in the items table.
    $customCategories = Item::whereNotNull('category')
        ->where('category', '!=', '')
        ->whereNotIn('category', $defaultCategories)
        ->distinct()
        ->orderBy('category')
        ->pluck('category');

    $customUnits = Item::whereNotNull('unit_type')
        ->where('unit_type', '!=', '')
        ->whereNotIn('unit_type', $defaultUnits)
        ->distinct()
        ->orderBy('unit_type')
        ->pluck('unit_type');

    $customSizes = Item::whereNotNull('size_weight')
        ->where('size_weight', '!=', '')
        ->whereNotIn('size_weight', $defaultSizes)
        ->distinct()
        ->orderBy('size_weight')
        ->pluck('size_weight');

    $customTargets = Item::whereNotNull('target_beneficiary')
        ->where('target_beneficiary', '!=', '')
        ->whereNotIn('target_beneficiary', $defaultTargets)
        ->distinct()
        ->orderBy('target_beneficiary')
        ->pluck('target_beneficiary');

    $customVariants = Item::whereNotNull('variant')
        ->where('variant', '!=', '')
        ->whereNotIn('variant', $defaultVariants)
        ->distinct()
        ->orderBy('variant')
        ->pluck('variant');

    $storageLocations = StorageLocation::availableInMambaling()->orderBy('name')->get();
    $existingSkus = Item::withTrashed()->pluck('sku')
        ->merge(Inventory::withTrashed()->pluck('sku'))
        ->filter()->unique()->values();
    return view('inventory.create', compact(
        'customCategories',
        'customUnits',
        'customSizes',
        'customTargets',
            'customVariants', 'storageLocations', 'existingSkus'
    ));
}
    public function store(Request $request, InventorySyncService $inventorySync, SkuGenerator $skuGenerator)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'unit_type' => 'required|string|max:100',
            'size_weight' => 'nullable|string|max:100',
            'target_beneficiary' => 'required|string|max:100',
            'variant' => 'nullable|string|max:100',
            'type' => 'required|in:consumable,reusable,returnable',
            'storage_location_id' => ['required', 'integer', Rule::exists('storage_locations', 'id')->where(fn ($query) => $query->where('is_active', true)->where('warehouse_name', 'like', '%Mambaling%'))],
            'min_stock' => 'nullable|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        $storageLocation = StorageLocation::availableInMambaling()->whereKey($validated['storage_location_id'])->firstOrFail();

        // "Reusable" is the user-facing term; keep the established database
        // value so existing inventory and API consumers remain compatible.
        $validated['type'] = $validated['type'] === 'reusable' ? 'returnable' : $validated['type'];
        $category = strtoupper($validated['category']);
        $unitType = strtoupper($validated['unit_type']);
        $usedSkus = Item::withTrashed()->pluck('sku')->merge(Inventory::withTrashed()->pluck('sku'))->filter()->all();
        $sku = $skuGenerator->generate(
            $category,
            $validated['name'],
            $validated['size_weight'] ?? null,
            $validated['variant'] ?? null,
            $validated['target_beneficiary'] ?? null,
            $validated['type'],
            $unitType,
            $usedSkus,
        );

        if (! $skuGenerator->isValidFormat($sku)) {
            return back()->withInput()->withErrors(['sku' => 'The generated SKU must contain 7 to 12 letters or numbers.']);
        }

        $item = Item::create([
            'name'               => $validated['name'],
            'category'           => $category,
            'unit_type'          => $unitType,
            'size_weight'        => $validated['size_weight'] ?? null,
            'target_beneficiary' => strtoupper($validated['target_beneficiary']),
            'variant'            => $validated['variant'] ?? 'NONE',
            'type'               => $validated['type'],
            'storage_location'   => $storageLocation->name,
            'min_stock'          => isset($validated['min_stock']) && $validated['min_stock'] !== '' ? (int) $validated['min_stock'] : 10,
            'expiration_date'    => $validated['expiration_date'] ?? null,
            'sku'                => $sku,
            'created_by_user_id' => $request->user()->id,
        ]);

        $inventorySync->syncItem($item);

        return redirect('/inventory')->with('success', 'Item added successfully.');
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);

        $defaultCategories = ['FOOD', 'MEDICAL', 'RESCUE', 'RELIEF'];

        $customCategories = Item::whereNotIn('category', $defaultCategories)
            ->distinct()->pluck('category');

        $storageLocations = StorageLocation::availableInMambaling()->orderBy('name')->get();
        return view('inventory.edit', compact('item', 'customCategories', 'storageLocations'));
    }

    public function update(Request $request, InventorySyncService $inventorySync, $id)
    {
        $item = Item::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'unit_type' => 'nullable|string|max:100',
            'size_weight' => 'nullable|string|max:100',
            'target_beneficiary' => 'nullable|string|max:100',
            'variant' => 'nullable|string|max:100',
            'type' => 'required|in:consumable,reusable,returnable',
            'storage_location_id' => ['required', 'integer', Rule::exists('storage_locations', 'id')->where(fn ($query) => $query->where('is_active', true)->where('warehouse_name', 'like', '%Mambaling%'))],
            'min_stock' => 'nullable|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        $storageLocation = StorageLocation::availableInMambaling()->whereKey($validated['storage_location_id'])->firstOrFail();

        if (($validated['type'] ?? null) === 'reusable') {
            $validated['type'] = 'returnable';
        }

        if (isset($validated['category'])) {
            $validated['category'] = strtoupper($validated['category']);
        }

        unset($validated['storage_location_id']);
        $validated['storage_location'] = $storageLocation->name;
        $item->update($validated);
        $inventorySync->syncItem($item->fresh('stockBatches'));

        return redirect('/inventory')->with('success', 'Item updated successfully.');
    }

    public function history(Item $item)
    {
        $item->load(['creator', 'stockBatches.creator']);
        $inventory = Inventory::withTrashed()->where('sku', $item->sku)->first();
        $events = collect();
        $historyBalanceComplete = true;
        $batchCreationLogs = ActivityLog::query()
            ->with('actor')
            ->where('subject_type', StockBatch::class)
            ->whereIn('subject_id', $item->stockBatches->pluck('id')->map(fn ($id): string => (string) $id))
            ->where('event_type', 'created')
            ->get()
            ->keyBy('subject_id');

        foreach ($item->stockBatches as $batch) {
            if ($batch->movement_type === 'return-good') {
                continue;
            }

            $createdMovement = $batchCreationLogs->get((string) $batch->id);
            $createdAttributes = $createdMovement?->properties['new'] ?? [];
            $recordedQuantity = $createdAttributes['quantity'] ?? null;
            $details = trim(($batch->supplier ?? '').($batch->date_received ? ' · Received '.$batch->date_received->format('M d, Y') : ''));
            if ($recordedQuantity === null) {
                $historyBalanceComplete = false;
                $recordedQuantity = (int) $batch->quantity;
                $details = trim($details.' · Original receipt quantity not available; showing current batch balance.');
            }
            $events->push([
                'date' => $createdMovement?->created_at ?? $batch->created_at,
                'kind' => $batch->movement_type === 'initial-import' ? 'Initial inventory balance' : 'Stock-in',
                'change' => (int) $recordedQuantity,
                'location' => $createdAttributes['storage_location'] ?? $batch->storage_location ?? 'Location not recorded',
                'user' => $createdMovement?->actor?->name ?? $batch->creator?->name ?? 'Not recorded',
                'details' => $details,
            ]);
        }

        if ($inventory) {
            foreach ($inventory->requests()->with(['approvedBy', 'rejectedBy'])->get() as $supplyRequest) {
                $requester = $supplyRequest->responder_email ?: $supplyRequest->source ?: 'Requester not recorded';
                $events->push(['date' => $supplyRequest->created_at, 'kind' => 'Request submitted', 'change' => 0, 'location' => $item->storage_location ?: 'Not recorded', 'user' => $requester, 'details' => trim(($supplyRequest->request_code ?? '').' · Quantity '.$supplyRequest->quantity.' · '.($supplyRequest->purpose ?? ''))]);
                if ($supplyRequest->approved_at) {
                    $events->push(['date' => $supplyRequest->approved_at, 'kind' => 'Request approved', 'change' => 0, 'location' => $item->storage_location ?: 'Not recorded', 'user' => $supplyRequest->approvedBy?->name ?? 'Not recorded', 'details' => 'Approved quantity: '.($supplyRequest->approved_quantity ?? $supplyRequest->quantity).($supplyRequest->approval_notes ? ' · '.$supplyRequest->approval_notes : '')]);
                }
                if ($supplyRequest->rejected_at) {
                    $events->push(['date' => $supplyRequest->rejected_at, 'kind' => 'Request rejected', 'change' => 0, 'location' => $item->storage_location ?: 'Not recorded', 'user' => $supplyRequest->rejectedBy?->name ?? 'Not recorded', 'details' => $supplyRequest->approval_notes ?? '']);
                }
            }

            foreach ($inventory->borrowReleases()->with(['processedBy', 'request'])->get() as $release) {
                $events->push(['date' => $release->released_at, 'kind' => 'Release', 'change' => -(int) $release->quantity, 'location' => $release->source_storage_location ?: 'Location not recorded', 'user' => $release->processedBy?->name ?? 'Not recorded', 'details' => trim(($release->request?->request_code ?? '').' · Destination: '.($release->location ?: 'Not recorded').' · '.($release->purpose ?? ''))]);
            }

            foreach ($inventory->returns()->with(['processedBy', 'receivedBy'])->get() as $return) {
                $change = $return->condition === 'Good' ? (int) $return->quantity : 0;
                $events->push(['date' => $return->returned_at ?? $return->created_at, 'kind' => 'Return ('.$return->condition.')', 'change' => $change, 'location' => $return->storage_location ?: 'Location not recorded', 'user' => $return->receivedBy?->name ?? $return->processedBy?->name ?? 'Not recorded', 'details' => trim('Returned by '.($return->returned_by_name ?: 'Not recorded').' · Quantity '.$return->quantity.($return->notes ? ' · '.$return->notes : ''))]);
            }
        }

        $remaining = 0;
        $events = $events->sortBy('date')->map(function (array $event) use (&$remaining, $historyBalanceComplete): array {
            $remaining = max(0, $remaining + $event['change']);
            $event['remaining'] = $historyBalanceComplete ? $remaining : null;
            $event['quantity'] = $event['change'] > 0 ? '+'.number_format($event['change']) : ($event['change'] < 0 ? '−'.number_format(abs($event['change'])) : '—');
            return $event;
        })->sortByDesc('date')->values();

        return view('inventory.history', compact('item', 'events'));
    }

    public function import(Request $request, InventorySyncService $inventorySync, SkuGenerator $skuGenerator)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:5120']);
        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        if (! $handle) return back()->withErrors(['csv_file' => 'The CSV file could not be read.']);
        $headers = fgetcsv($handle);
        $required = ['name','category','unit_type','size_weight','target_beneficiary','variant','type','storage_location','quantity','min_stock','expiration_date'];
        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $headers ?: []);
        if ($headers !== $required) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'CSV headers must be exactly: '.implode(',', $required)]);
        }
        $locations = StorageLocation::availableInMambaling()->pluck('name')->all();
        $usedSkus = Item::withTrashed()->pluck('sku')->merge(Inventory::withTrashed()->pluck('sku'))->filter()->map(fn ($sku) => strtoupper($sku))->all();
        $validRows = []; $errors = []; $rowNumber = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if ($values === [null] || $values === []) continue;
            if ($rowNumber > 1001) { $errors[] = "Row {$rowNumber}: files may contain at most 1000 data rows."; break; }
            if (count($values) !== count($required)) { $errors[] = "Row {$rowNumber}: expected ".count($required).' columns.'; continue; }
            $row = array_combine($required, $values);
            $validator = Validator::make($row, [
                'name'=>'required|string|max:255','category'=>'required|string|max:100','unit_type'=>'required|string|max:100',
                'size_weight'=>'nullable|string|max:100','target_beneficiary'=>'required|string|max:100','variant'=>'nullable|string|max:100',
                'type'=>'required|in:consumable,reusable,returnable','storage_location'=>['required',Rule::in($locations)],
                'quantity'=>'required|integer|min:0|max:2147483647','min_stock'=>'nullable|integer|min:0', 'expiration_date'=>'nullable|date',
            ]);
            if ($validator->fails()) { foreach ($validator->errors()->all() as $error) $errors[] = "Row {$rowNumber}: {$error}"; continue; }
            $row['_line'] = $rowNumber;
            $row['_sku'] = $skuGenerator->generate(strtoupper($row['category']), $row['name'], $row['size_weight'] ?: null, $row['variant'] ?: null, $row['target_beneficiary'], $row['type'] === 'reusable' ? 'returnable' : $row['type'], strtoupper($row['unit_type']), $usedSkus);
            if (! $skuGenerator->isValidFormat($row['_sku'])) { $errors[] = "Row {$rowNumber}: generated SKU is outside the allowed format."; continue; }
            $usedSkus[] = $row['_sku'];
            $validRows[] = $row;
        }
        fclose($handle);
        $imported = 0;
        try {
            DB::transaction(function () use ($validRows, $request, $inventorySync, &$imported) {
                foreach ($validRows as $row) {
                    $item = Item::create([
                        'name'=>$row['name'],'category'=>strtoupper($row['category']),'unit_type'=>strtoupper($row['unit_type']),
                        'size_weight'=>$row['size_weight'] ?: null,'target_beneficiary'=>strtoupper($row['target_beneficiary']),
                        'variant'=>$row['variant'] ?: 'NONE','type'=>$row['type'] === 'reusable' ? 'returnable' : $row['type'],
                        'storage_location'=>$row['storage_location'],'min_stock'=>$row['min_stock'] === '' ? 10 : (int) $row['min_stock'],
                        'expiration_date'=>$row['expiration_date'] ?: null,'sku'=>$row['_sku'],'created_by_user_id'=>$request->user()->id,
                    ]);
                    if ((int) $row['quantity'] > 0) {
                        $item->stockBatches()->create(['quantity'=>(int) $row['quantity'],'supplier'=>'Initial inventory import','date_received'=>now()->toDateString(),
                            'expiration_date'=>$row['expiration_date'] ?: null,'created_by_user_id'=>$request->user()->id,
                            'movement_type'=>'initial-import','storage_location'=>$row['storage_location']]);
                    }
                    $inventorySync->syncItem($item->fresh('stockBatches'));
                    $imported++;
                }
            });
        } catch (\Throwable $exception) {
            report($exception);
            return back()->with('import_summary', ['imported'=>0,'skipped'=>count($errors),'failed'=>count($validRows),'errors'=>['Import transaction rolled back due to a database error.']]);
        }
        return back()->with('import_summary', ['imported'=>$imported,'skipped'=>count($errors),'failed'=>0,'errors'=>$errors]);
    }

    public function delete(InventorySyncService $inventorySync, $id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        $inventorySync->deleteInventoryForItem($item);

        return redirect('/inventory')->with('success', 'Item deleted successfully.');
    }
}
