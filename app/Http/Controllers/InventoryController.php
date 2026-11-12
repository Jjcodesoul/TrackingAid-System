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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

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

        $allItems = Item::with(['storageLocation', 'stockBatches.storageLocation'])
            ->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);

        if ($selectedLocation) {
            $allItems = $allItems->filter(function (Item $item) use ($selectedLocation): bool {
                $allBatches = $item->stockBatches;
                $locationBatches = $allBatches->filter(fn (StockBatch $batch): bool =>
                    ($batch->storageLocation?->name ?? $batch->storage_location) === $selectedLocation->name
                );
                $item->setRelation('stockBatches', $locationBatches->values());

                return $allBatches->isEmpty()
                    ? ($item->storageLocation?->name ?? $item->storage_location) === $selectedLocation->name
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

    
public function create(Request $request)
{
    abort_unless(in_array($request->user()?->role, ['admin', 'super_admin'], true), 403);

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

    $storageLocation = StorageLocation::designatedMambaling()->where('is_active', true)->firstOrFail();
    $existingSkus = Item::withTrashed()->pluck('sku')
        ->merge(Inventory::withTrashed()->pluck('sku'))
        ->filter()->unique()->values();
    return view('inventory.create', compact(
        'customCategories',
        'customUnits',
        'customSizes',
        'customTargets',
            'customVariants', 'storageLocation', 'existingSkus'
    ));
}
    public function store(Request $request, InventorySyncService $inventorySync, SkuGenerator $skuGenerator)
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'super_admin'], true), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'unit_type' => 'required|string|max:100',
            'size_weight' => 'nullable|string|max:100',
            'target_beneficiary' => 'required|string|max:100',
            'variant' => 'nullable|string|max:100',
            'type' => 'required|in:consumable,reusable,returnable',
            'min_stock' => 'nullable|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        $storageLocation = StorageLocation::designatedMambaling()->where('is_active', true)->firstOrFail();

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

        return view('inventory.edit', compact('item', 'customCategories'));
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
            'min_stock' => 'nullable|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        if (($validated['type'] ?? null) === 'reusable') {
            $validated['type'] = 'returnable';
        }

        if (isset($validated['category'])) {
            $validated['category'] = strtoupper($validated['category']);
        }

        // Existing assignments are historical text values and are retained until manually verified.
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

            foreach ($inventory->borrowReleases()->with(['processedBy', 'request', 'forwardedRequest'])->get() as $release) {
                $requestReference = $release->request?->request_code ?? $release->forwardedRequest?->tracking_reference ?? '';
                $events->push(['date' => $release->released_at, 'kind' => 'Release', 'change' => -(int) $release->quantity, 'location' => $release->source_storage_location ?: 'Location not recorded', 'user' => $release->processedBy?->name ?? 'Not recorded', 'details' => trim($requestReference.' · Destination: '.($release->location ?: 'Not recorded').' · '.($release->purpose ?? ''))]);
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
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'The CSV file is empty.']);
        }
        $delimiters = [',' => substr_count($firstLine, ','), ';' => substr_count($firstLine, ';'), "\t" => substr_count($firstLine, "\t")];
        $delimiter = array_search(max($delimiters), $delimiters, true);
        rewind($handle);
        $rawHeaders = fgetcsv($handle, 0, $delimiter) ?: [];
        $aliases = [
            'item' => 'name', 'item_name' => 'name', 'item_description' => 'name', 'product_name' => 'name',
            'item_code' => 'sku', 'product_code' => 'sku', 'sku_code' => 'sku',
            'id' => 'item_id', 'product_id' => 'item_id',
            'unit' => 'unit_type', 'unit_of_measure' => 'unit_type', 'uom' => 'unit_type',
            'size' => 'size_weight', 'weight' => 'size_weight',
            'beneficiary' => 'target_beneficiary', 'target' => 'target_beneficiary',
            'item_type' => 'type', 'stock' => 'quantity', 'qty' => 'quantity', 'total_stock' => 'quantity',
            'reorder_level' => 'min_stock', 'minimum_stock' => 'min_stock',
            'expiry' => 'expiration_date', 'expiry_date' => 'expiration_date', 'expiration' => 'expiration_date',
            'storage' => 'storage_location', 'location' => 'storage_location',
            'received_date' => 'date_received', 'supplier_name' => 'supplier',
        ];
        $headers = array_map(function ($header) use ($aliases): string {
            $normalized = strtolower(trim((string) $header));
            $normalized = preg_replace('/[^a-z0-9]+/', '_', preg_replace('/^\xEF\xBB\xBF/', '', $normalized));
            $normalized = trim($normalized, '_');

            return $aliases[$normalized] ?? $normalized;
        }, $rawHeaders);
        $required = ['name', 'category', 'unit_type', 'quantity'];
        $supported = [...$required, 'sku', 'item_id', 'inventory_id', 'size_weight', 'target_beneficiary', 'variant', 'type', 'storage_location', 'min_stock', 'expiration_date', 'date_received', 'supplier'];
        if (array_diff($required, $headers) || array_diff($headers, $supported) || count($headers) !== count(array_unique($headers))) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'CSV must contain one each of these columns: name, category, unit_type, quantity. Optional columns: sku, item_id, inventory_id, size_weight, target_beneficiary, variant, type, min_stock, expiration_date, date_received, supplier, storage_location.']);
        }
        $storageLocation = StorageLocation::designatedMambaling()->where('is_active', true)->first();
        if (! $storageLocation) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'Inventory import is unavailable because the fixed Mambaling Storage location is not configured as active. Ask an administrator to restore the storage configuration.']);
        }
        $usedSkus = Item::withTrashed()->pluck('sku')->merge(Inventory::withTrashed()->pluck('sku'))->filter()->map(fn ($sku) => strtoupper($sku))->all();
        $validRows = []; $errors = []; $skippedRows = []; $rowNumber = 1; $rowsProcessed = 0;
        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;
            if ($values === [null] || $values === []) continue;
            $rowsProcessed++;
            if ($rowNumber > 1001) { $errors[] = "Row {$rowNumber}: files may contain at most 1000 data rows."; $skippedRows[$rowNumber] = true; break; }
            if (count($values) !== count($headers)) { $errors[] = "Row {$rowNumber}: expected ".count($headers).' columns.'; $skippedRows[$rowNumber] = true; continue; }
            $row = array_combine($headers, $values);
            $row = array_merge([
                'size_weight' => '',
                'target_beneficiary' => 'ALL',
                'variant' => 'NONE',
                'type' => 'consumable',
                'min_stock' => '',
                'expiration_date' => '',
                'sku' => '',
                'item_id' => '',
                'inventory_id' => '',
                'date_received' => now()->toDateString(),
                'supplier' => 'CSV inventory import',
            ], $row);
            foreach (['size_weight', 'target_beneficiary', 'variant', 'type', 'min_stock', 'expiration_date', 'sku', 'item_id', 'inventory_id', 'date_received', 'supplier'] as $optionalField) {
                if (! isset($row[$optionalField]) || trim((string) $row[$optionalField]) === '') {
                    $row[$optionalField] = match ($optionalField) {
                        'target_beneficiary' => 'ALL',
                        'variant' => 'NONE',
                        'type' => 'consumable',
                        'date_received' => now()->toDateString(),
                        'supplier' => 'CSV inventory import',
                        default => '',
                    };
                }
            }
            $row['storage_location'] = $storageLocation->name;
            $row['type'] = strtolower(trim((string) $row['type']));
            $row['_headers'] = $headers;
            $validator = Validator::make($row, [
                'name'=>'required|string|max:255','category'=>'required|string|max:100','unit_type'=>'required|string|max:100',
                'size_weight'=>'nullable|string|max:100','target_beneficiary'=>'required|string|max:100','variant'=>'nullable|string|max:100',
                'type'=>'required|in:consumable,reusable,returnable',
                'quantity'=>'required|integer|min:0|max:2147483647','min_stock'=>'nullable|integer|min:0', 'expiration_date'=>'nullable|date',
                'sku'=>'nullable|string|max:100','item_id'=>'nullable|integer|min:1','inventory_id'=>'nullable|integer|min:1',
                'date_received'=>'required|date','supplier'=>'nullable|string|max:255',
            ]);
            if ($validator->fails()) { foreach ($validator->errors()->all() as $error) $errors[] = "Row {$rowNumber}: {$error}"; $skippedRows[$rowNumber] = true; continue; }
            $row['_line'] = $rowNumber;
            $validRows[] = $row;
        }
        fclose($handle);
        $imported = 0;
        $createdItems = 0;
        $updatedItems = 0;
        $stockAdded = 0;
        $failedRows = 0;
        try {
            DB::transaction(function () use ($validRows, $rowsProcessed, $request, $inventorySync, $skuGenerator, &$usedSkus, &$errors, &$skippedRows, &$imported, &$createdItems, &$updatedItems, &$stockAdded) {
                foreach ($validRows as $row) {
                    $normalizedType = $row['type'] === 'reusable' ? 'returnable' : $row['type'];
                    $match = $this->resolveCsvItem($row, $normalizedType);
                    if ($match['error'] !== null) {
                        $errors[] = "Row {$row['_line']}: {$match['error']}";
                        $skippedRows[$row['_line']] = true;
                        continue;
                    }

                    $item = $match['item'];
                    $isNewItem = $item === null;
                    if ($isNewItem) {
                        $sku = strtoupper(trim((string) $row['sku']));
                        if ($sku === '') {
                            $sku = $skuGenerator->generate(
                                strtoupper($row['category']),
                                $row['name'],
                                $row['size_weight'] ?: null,
                                $row['variant'] ?: null,
                                $row['target_beneficiary'],
                                $normalizedType,
                                strtoupper($row['unit_type']),
                                $usedSkus,
                            );
                        }
                        if (! $skuGenerator->isValidFormat($sku)) {
                            $errors[] = "Row {$row['_line']}: the supplied or generated SKU is outside the allowed format.";
                            $skippedRows[$row['_line']] = true;
                            continue;
                        }
                        if (in_array(mb_strtolower($sku), array_map('mb_strtolower', $usedSkus), true)) {
                            $errors[] = "Row {$row['_line']}: SKU {$sku} is already assigned to another inventory record.";
                            $skippedRows[$row['_line']] = true;
                            continue;
                        }
                        $usedSkus[] = $sku;
                        $item = Item::create([
                            'name' => trim($row['name']),
                            'category' => strtoupper(trim($row['category'])),
                            'unit_type' => strtoupper(trim($row['unit_type'])),
                            'size_weight' => $row['size_weight'] ?: null,
                            'target_beneficiary' => strtoupper(trim($row['target_beneficiary'])),
                            'variant' => strtoupper(trim($row['variant'] ?: 'NONE')),
                            'type' => $normalizedType,
                            'storage_location' => $row['storage_location'],
                            'min_stock' => $row['min_stock'] === '' ? 10 : (int) $row['min_stock'],
                            'expiration_date' => $row['expiration_date'] ?: null,
                            'sku' => $sku,
                            'created_by_user_id' => $request->user()->id,
                        ]);
                        $createdItems++;
                    } else {
                        if ($item->trashed()) {
                            $errors[] = "Row {$row['_line']}: the matched item #{$item->id} ({$item->sku}) is deactivated; restore it before importing stock.";
                            $skippedRows[$row['_line']] = true;
                            continue;
                        }
                        $updatedItems++;
                    }

                    if ((int) $row['quantity'] > 0) {
                        $inventorySync->receiveStock(
                            (int) $item->id,
                            (int) $row['quantity'],
                            (int) $request->user()->id,
                            $row['date_received'],
                            $row['expiration_date'] ?: null,
                            $row['supplier'] ?: 'CSV inventory import',
                            (string) Str::uuid(),
                            $isNewItem ? 'initial-import' : 'stock-in',
                        );
                        $stockAdded += (int) $row['quantity'];
                    } else {
                        $inventorySync->syncItem($item->fresh('stockBatches'));
                    }
                    $imported++;
                }

                if ($imported > 0) {
                    ActivityLog::create([
                        'actor_id' => Auth::id(),
                        'event_type' => 'csv_imported',
                        'subject_type' => Item::class,
                        'subject_id' => 'csv-import',
                        'properties' => [
                            'action' => 'Imported inventory items from CSV',
                            'new' => [
                                'item_count' => $imported,
                                'created_item_count' => $createdItems,
                                'updated_item_count' => $updatedItems,
                                'stock_added' => $stockAdded,
                                'rows_processed' => $rowsProcessed,
                            ],
                        ],
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            report($exception);
            $validLineNumbers = array_column($validRows, '_line');
            $failedLineNumbers = array_diff($validLineNumbers, array_keys($skippedRows));
            $failedRows = count($failedLineNumbers);
            $imported = $createdItems = $updatedItems = $stockAdded = 0;
            foreach ($failedLineNumbers as $lineNumber) {
                $errors[] = "Row {$lineNumber}: the import transaction failed and this row was rolled back; no stock changes were saved. Failure details were recorded in the server log.";
            }
        }
        return back()->with('import_summary', [
            'rows_processed' => $rowsProcessed,
            'imported' => $imported,
            'created' => $createdItems,
            'updated' => $updatedItems,
            'stock_added' => $stockAdded,
            'skipped' => count($skippedRows),
            'failed' => $failedRows,
            'errors' => $errors,
        ]);
    }

    private function resolveCsvItem(array $row, string $normalizedType): array
    {
        $itemMatches = collect();
        $inventoryWithoutItem = null;

        if (trim((string) $row['item_id']) !== '') {
            $item = Item::withTrashed()->find((int) $row['item_id']);
            if (! $item) {
                return ['item' => null, 'error' => "item ID {$row['item_id']} does not exist."];
            }
            $itemMatches->push($item);
        }

        if (trim((string) $row['inventory_id']) !== '') {
            $inventory = Inventory::withTrashed()->find((int) $row['inventory_id']);
            if (! $inventory) {
                return ['item' => null, 'error' => "inventory ID {$row['inventory_id']} does not exist."];
            }
            $linkedItem = Item::withTrashed()
                ->whereRaw('LOWER(TRIM(sku)) = ?', [mb_strtolower(trim($inventory->sku))])
                ->first();
            if (! $linkedItem) {
                return ['item' => null, 'error' => "inventory record #{$inventory->id} (SKU {$inventory->sku}) has no linked catalog item; stock was not changed."];
            }
            $itemMatches->push($linkedItem);
        }

        $sku = trim((string) $row['sku']);
        if ($sku !== '') {
            $skuItems = Item::withTrashed()
                ->whereRaw('LOWER(TRIM(sku)) = ?', [mb_strtolower($sku)])
                ->get();
            $skuInventories = Inventory::withTrashed()
                ->whereRaw('LOWER(TRIM(sku)) = ?', [mb_strtolower($sku)])
                ->get();
            foreach ($skuInventories as $inventory) {
                $linkedItem = Item::withTrashed()
                    ->whereRaw('LOWER(TRIM(sku)) = ?', [mb_strtolower(trim($inventory->sku))])
                    ->first();
                if (! $linkedItem) {
                    $inventoryWithoutItem = $inventory;
                } else {
                    $skuItems->push($linkedItem);
                }
            }
            $itemMatches = $itemMatches->merge($skuItems)->unique('id')->values();

            if ($itemMatches->isEmpty() && $inventoryWithoutItem) {
                return ['item' => null, 'error' => "SKU {$sku} matches inventory record #{$inventoryWithoutItem->id}, but that record has no linked catalog item; stock was not changed."];
            }
            if ($itemMatches->isEmpty()) {
                $nameMatches = $this->findCsvNameMatches($row, $normalizedType);
                if ($nameMatches->isNotEmpty()) {
                    return ['item' => null, 'error' => "SKU {$sku} is not registered, but the item description matches ". $this->describeCsvCandidates($nameMatches).'; correct the SKU or use its registered SKU before importing.'];
                }

                return ['item' => null, 'error' => null];
            }
        }

        $itemMatches = $itemMatches->unique('id')->values();
        if ($itemMatches->count() > 1) {
            return ['item' => null, 'error' => 'the supplied identifiers point to different catalog records: '.$this->describeCsvCandidates($itemMatches).'.'];
        }
        if ($itemMatches->isNotEmpty()) {
            $item = $itemMatches->first();
            if ($sku !== '' && $this->normalizeCsvValue($item->sku) !== $this->normalizeCsvValue($sku)) {
                return ['item' => null, 'error' => "the supplied SKU {$sku} conflicts with item ID {$item->id} (registered SKU {$item->sku})."];
            }

            return ['item' => $item, 'error' => null];
        }

        $matches = $this->findCsvNameMatches($row, $normalizedType);
        if ($matches->count() > 1) {
            return ['item' => null, 'error' => 'multiple records match this name/category/unit; no stock was added. Candidates: '.$this->describeCsvCandidates($matches).'.'];
        }
        if ($matches->isNotEmpty()) {
            return ['item' => $matches->first(), 'error' => null];
        }

        $inventoryMatches = Inventory::withTrashed()
            ->whereRaw('LOWER(TRIM(category)) = ?', [mb_strtolower(trim($row['category']))])
            ->get()
            ->filter(fn (Inventory $inventory): bool =>
                $this->normalizeCsvValue($inventory->name) === $this->normalizeCsvValue($row['name'])
            );
        if ($inventoryMatches->isNotEmpty()) {
            $candidateIds = $inventoryMatches->map(fn (Inventory $inventory): string =>
                "#{$inventory->id} SKU {$inventory->sku} ({$inventory->name})"
            )->implode('; ');
            return ['item' => null, 'error' => "matching inventory records lack corresponding catalog items: {$candidateIds}; stock was not changed."];
        }

        return ['item' => null, 'error' => null];
    }

    private function findCsvNameMatches(array $row, string $normalizedType): Collection
    {
        return Item::withTrashed()
            ->whereRaw('LOWER(TRIM(category)) = ?', [mb_strtolower(trim($row['category']))])
            ->whereRaw('LOWER(TRIM(unit_type)) = ?', [mb_strtolower(trim($row['unit_type']))])
            ->get()
            ->filter(function (Item $item) use ($row, $normalizedType): bool {
                if ($this->normalizeCsvValue($item->name) !== $this->normalizeCsvValue($row['name'])) {
                    return false;
                }

                foreach (['size_weight', 'target_beneficiary', 'variant'] as $field) {
                    if (in_array($field, $row['_headers'], true) && trim((string) $row[$field]) !== ''
                        && $this->normalizeCsvValue($item->{$field}) !== $this->normalizeCsvValue($row[$field])) {
                        return false;
                    }
                }

                if (in_array('type', $row['_headers'], true) && trim((string) ($row['type'] ?? '')) !== '') {
                    $itemType = $item->type === 'reusable' ? 'returnable' : $item->type;
                    if ($itemType !== $normalizedType) {
                        return false;
                    }
                }

                return true;
            })->values();
    }

    private function normalizeCsvValue(?string $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim((string) $value)));
    }

    private function describeCsvCandidates(Collection $items): string
    {
        return $items->take(5)->map(fn (Item $item): string =>
            "#{$item->id} SKU {$item->sku} - {$item->name} [{$item->category}, {$item->unit_type}, size ".($item->size_weight ?: 'n/a').", variant ".($item->variant ?: 'n/a').", {$item->type}]"
        )->implode('; ').($items->count() > 5 ? '; and '.($items->count() - 5).' more' : '');
    }

    public function delete(InventorySyncService $inventorySync, $id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        $inventorySync->deleteInventoryForItem($item);

        return redirect('/inventory')->with('success', 'Item deleted successfully.');
    }
}
