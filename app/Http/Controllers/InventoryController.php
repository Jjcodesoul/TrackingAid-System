<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Services\InventorySyncService;

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
            'lowStock'   => $allItems->filter(fn (Item $item): bool => $stockFor($item) > 0 && $stockFor($item) < 10)->count(),
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

        $customCategories = Item::whereNotIn('category', $defaultCategories)
            ->distinct()->pluck('category');

        $customUnits = Item::whereNotNull('unit_type')->where('unit_type', '!=', '')
            ->whereNotIn('unit_type', $defaultUnits)
            ->distinct()->pluck('unit_type');

        $customSizes = Item::whereNotNull('size_weight')->where('size_weight', '!=', '')
            ->whereNotIn('size_weight', $defaultSizes)
            ->distinct()->pluck('size_weight');

        $customTargets = Item::whereNotNull('target_beneficiary')->where('target_beneficiary', '!=', '')
            ->whereNotIn('target_beneficiary', $defaultTargets)
            ->distinct()->pluck('target_beneficiary');

        $customVariants = Item::whereNotNull('variant')->where('variant', '!=', '')
            ->whereNotIn('variant', $defaultVariants)
            ->distinct()->pluck('variant');

        return view('inventory.create', compact(
            'customCategories', 'customUnits', 'customSizes', 'customTargets', 'customVariants'
        ));
    }

    public function store(Request $request, InventorySyncService $inventorySync)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'unit_type' => 'required|string|max:100',
            'size_weight' => 'nullable|string|max:100',
            'target_beneficiary' => 'required|string|max:100',
            'variant' => 'nullable|string|max:100',
            'type' => 'required|in:consumable,returnable',
            'storage_location' => 'required|string|max:255',
            'expiration_date' => 'nullable|date',
        ]);

        $category = strtoupper($validated['category']);
        $unitType = strtoupper($validated['unit_type']);

        // SKU format: [2-letter category code][3-letter item code][3-digit sequence]
        // e.g. "FDCOR001" — keeps every SKU at a fixed, short 8 characters,
        // well inside the 7-12 character range.
        $categoryCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $validated['category']), 0, 2));
        $firstWord = preg_split('/\s+/', trim($validated['name']))[0] ?? '';
        $itemCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $firstWord), 0, 3));

        // Pad codes that end up shorter than expected (e.g. a 1-letter category
        // or item name) so the format stays consistent.
        $categoryCode = str_pad($categoryCode, 2, 'X');
        $itemCode = str_pad($itemCode, 3, 'X');

        $prefix = $categoryCode . $itemCode;

        // Next sequence number for this exact category+item code combination —
        // this is what guarantees uniqueness, not text matching, so two items
        // with the same name/category (e.g. different sizes of the same
        // product) simply get 001, 002, 003 automatically.
        $existingCount = Item::where('sku', 'like', $prefix . '%')->count();
        $sequence = $existingCount + 1;
        $sku = $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);

        // Safety net in case of a race condition (two people saving at the
        // same instant) — keep bumping until we find a free slot.
        while (Item::where('sku', $sku)->exists()) {
            $sequence++;
            $sku = $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
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
            'type' => 'required|in:consumable,returnable',
            'storage_location' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date',
        ]);

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