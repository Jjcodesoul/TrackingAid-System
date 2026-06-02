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

   public function store(Request $request)
{
    // AUTO GENERATE SKU AGAIN (BACKEND SAFE)

    $sku =
        strtoupper($request->category) . '-' .
        strtoupper(str_replace(' ', '-', $request->name)) . '-' .
        strtoupper($request->unit_type);

    if ($request->size_weight) {

        $sku .= '-' .
            strtoupper(str_replace(' ', '', $request->size_weight));
    }

    if ($request->target_beneficiary) {

        $sku .= '-' .
            strtoupper($request->target_beneficiary);
    }

    if ($request->variant) {

        $sku .= '-' .
            strtoupper(str_replace(' ', '-', $request->variant));
    }

    // CHECK DUPLICATE SKU
    if (Item::where('sku', $sku)->exists()) {

        return back()->with(
            'error',
            'SKU already exists.'
        );
    }

    // SAVE
    Item::create([

        'name' => $request->name,

        'category' => $request->category,

        'unit_type' => $request->unit_type,

        'size_weight' => $request->size_weight,

        'target_beneficiary' => $request->target_beneficiary,

        'variant' => $request->variant,

        // FORCE LOWERCASE
        'type' => strtolower($request->type),

        'storage_location' => $request->storage_location,

        // USE GENERATED SKU
        'sku' => $sku,

        // DEFAULT STOCK
        'total_stock' => 0,

        'stock_status' => 'Out of Stock',
    ]);

    return redirect('/inventory')
        ->with('success', 'Item added successfully.');
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

        return redirect('/inventory')->with('success', 'Item updated');
    }

    public function delete($id)
    {
        $item = Item::findOrFail($id);
        $item->delete(); // soft delete
        return redirect('/inventory')->with('success', 'Item deleted');
    }
}