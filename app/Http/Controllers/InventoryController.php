<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Services\InventorySyncService;

class InventoryController extends Controller
{
    public function index()
    {
        $items = Item::with('stockBatches')->latest()->get();
        return view('inventory.index', compact('items'));
    }

    public function create()
    {
        return view('inventory.create');
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

        $sku = $category . '-' .
               strtoupper(str_replace(' ', '-', $validated['name'])) . '-' .
               $unitType;

        if (! empty($validated['size_weight'])) {
            $sku .= '-' . strtoupper(str_replace(' ', '', $validated['size_weight']));
        }

        if (! empty($validated['target_beneficiary'])) {
            $sku .= '-' . strtoupper($validated['target_beneficiary']);
        }

        if (! empty($validated['variant']) && strtoupper($validated['variant']) !== 'NONE') {
            $sku .= '-' . strtoupper(str_replace(' ', '-', $validated['variant']));
        }

        if (Item::where('sku', $sku)->exists()) {
            return back()->with('error', 'SKU already exists. Please change item details.');
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
        return view('inventory.edit', compact('item'));
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
