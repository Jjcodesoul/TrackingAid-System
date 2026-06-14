<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

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

    public function store(Request $request)
    {
        $sku = strtoupper($request->category) . '-' .
               strtoupper(str_replace(' ', '-', $request->name)) . '-' .
               strtoupper($request->unit_type);

        if ($request->size_weight) {
            $sku .= '-' . strtoupper(str_replace(' ', '', $request->size_weight));
        }

        if ($request->target_beneficiary) {
            $sku .= '-' . strtoupper($request->target_beneficiary);
        }

        if ($request->variant && $request->variant !== 'NONE') {
            $sku .= '-' . strtoupper(str_replace(' ', '-', $request->variant));
        }

        if (Item::where('sku', $sku)->exists()) {
            return back()->with('error', 'SKU already exists. Please change item details.');
        }

        Item::create([
            'name'               => $request->name,
            'category'           => $request->category,
            'unit_type'          => $request->unit_type,
            'size_weight'        => $request->size_weight,
            'target_beneficiary' => $request->target_beneficiary,
            'variant'            => $request->variant,
            'type'               => strtolower($request->type),
            'storage_location'   => $request->storage_location,
            'expiration_date'    => $request->expiration_date,
            'sku'                => $sku,
        ]);

        return redirect('/inventory')->with('success', 'Item added successfully.');
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        return view('inventory.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $item->update($request->all());
        return redirect('/inventory')->with('success', 'Item updated successfully.');
    }

    public function delete($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        return redirect('/inventory')->with('success', 'Item deleted successfully.');
    }
}
