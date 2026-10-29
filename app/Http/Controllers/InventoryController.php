<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Item;
use App\Services\InventorySyncService;
use App\Services\SkuGenerator;
use App\Models\StorageLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index()
    {
        $allItems = Item::with('stockBatches')
            ->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);

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

        $storageLocations = StorageLocation::where('is_active', true)->orderBy('name')->get();
        $unverifiedLocations = Item::whereNotNull('storage_location')->where('storage_location', '!=', '')
            ->whereNotIn('storage_location', $storageLocations->pluck('name'))->distinct()->orderBy('storage_location')->pluck('storage_location');
        $unverifiedLocations = $unverifiedLocations->merge(Inventory::whereNotNull('storage_location')->where('storage_location', '!=', '')
            ->whereNotIn('storage_location', $storageLocations->pluck('name'))->distinct()->orderBy('storage_location')->pluck('storage_location'))->unique()->sort()->values();
        $missingLocationCount = Item::whereNull('storage_location')->orWhere('storage_location', '')->count();

        return view('inventory.index', compact('items', 'inventoryStats', 'storageLocations', 'unverifiedLocations', 'missingLocationCount'));
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

    $storageLocations = StorageLocation::where('is_active', true)->orderBy('name')->get();
    return view('inventory.create', compact(
        'customCategories',
        'customUnits',
        'customSizes',
        'customTargets',
        'customVariants', 'storageLocations'
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
            'storage_location' => ['required', 'string', 'max:255', Rule::exists('storage_locations', 'name')->where('is_active', true)],
            'min_stock' => 'nullable|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        // "Reusable" is the user-facing term; keep the established database
        // value so existing inventory and API consumers remain compatible.
        $validated['type'] = $validated['type'] === 'reusable' ? 'returnable' : $validated['type'];
        $category = strtoupper($validated['category']);
        $unitType = strtoupper($validated['unit_type']);
        $sku = $skuGenerator->generate(
            $category,
            $validated['name'],
            $validated['size_weight'] ?? null,
            $validated['variant'] ?? null,
            $validated['target_beneficiary'] ?? null,
            $validated['type'],
        );

        if (Item::withTrashed()->where('sku', $sku)->exists()
            || Inventory::withTrashed()->where('sku', $sku)->exists()) {
            return back()->withInput()->withErrors([
                'sku' => "The generated SKU {$sku} already exists. Use a distinct item name, size, variant, or beneficiary, or update the existing item.",
            ]);
        }

        $item = Item::create([
            'name'               => $validated['name'],
            'category'           => $category,
            'unit_type'          => $unitType,
            'size_weight'        => $validated['size_weight'] ?? null,
            'target_beneficiary' => strtoupper($validated['target_beneficiary']),
            'variant'            => $validated['variant'] ?? 'NONE',
            'type'               => $validated['type'],
            'storage_location'   => $validated['storage_location'],
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

        $storageLocations = StorageLocation::where('is_active', true)->orderBy('name')->get();
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
            'storage_location' => ['required', 'string', 'max:255', Rule::exists('storage_locations', 'name')->where('is_active', true)],
            'min_stock' => 'nullable|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        if (($validated['type'] ?? null) === 'reusable') {
            $validated['type'] = 'returnable';
        }

        if (isset($validated['category'])) {
            $validated['category'] = strtoupper($validated['category']);
        }

        $item->update($validated);
        $inventorySync->syncItem($item->fresh('stockBatches'));

        return redirect('/inventory')->with('success', 'Item updated successfully.');
    }

    public function storeLocation(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:storage_locations,name']);
        StorageLocation::create($validated + ['warehouse_name' => 'Mambaling, Cebu City, Philippines', 'is_active' => true]);
        return back()->with('success', 'Verified storage location added under Mambaling, Cebu City.');
    }

    public function history(Item $item)
    {
        $item->load(['creator', 'stockBatches.creator']);
        $inventory = Inventory::withTrashed()->where('sku', $item->sku)->first();
        $releases = $inventory ? $inventory->borrowReleases()->with('processedBy')->orderByDesc('released_at')->get() : collect();
        $returns = $inventory ? $inventory->returns()->with('processedBy')->latest()->get() : collect();
        return view('inventory.history', compact('item', 'releases', 'returns'));
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
        $locations = StorageLocation::where('is_active', true)->pluck('name')->all();
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
            $row['_sku'] = $skuGenerator->generate(strtoupper($row['category']), $row['name'], $row['size_weight'] ?: null, $row['variant'] ?: null, $row['target_beneficiary'], $row['type'] === 'reusable' ? 'returnable' : $row['type']);
            if (Item::withTrashed()->where('sku', $row['_sku'])->exists() || Inventory::withTrashed()->where('sku', $row['_sku'])->exists()) {
                $errors[] = "Row {$rowNumber}: generated SKU {$row['_sku']} already exists."; continue;
            }
            if (collect($validRows)->contains(fn ($valid) => $valid['_sku'] === $row['_sku'])) { $errors[] = "Row {$rowNumber}: duplicate item in this CSV (SKU {$row['_sku']})."; continue; }
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
