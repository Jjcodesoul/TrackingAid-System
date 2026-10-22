<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Item;
use App\Services\InventorySyncService;
use App\Services\SkuGenerator;

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

        return view('inventory.index', compact('items', 'inventoryStats'));
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

    return view('inventory.create', compact(
        'customCategories',
        'customUnits',
        'customSizes',
        'customTargets',
        'customVariants'
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
            'storage_location' => 'required|string|max:255',
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
            'storage_location' => 'nullable|string|max:255',
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

    public function delete(InventorySyncService $inventorySync, $id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        $inventorySync->deleteInventoryForItem($item);

        return redirect('/inventory')->with('success', 'Item deleted successfully.');
    }
}
