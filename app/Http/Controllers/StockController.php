<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\StockBatch;
use App\Services\InventorySyncService;

class StockController extends Controller
{
    public function create()
    {
        $items = Item::all()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $categories = Item::distinct()->pluck('category')->sort()->values();

        return view('stock.create', compact('items', 'categories'));
    }

    public function store(Request $request, InventorySyncService $inventorySync)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'supplier' => 'nullable|string|max:255',
            'date_received' => 'required|date',
            'expiration_date' => 'nullable|date',
        ]);

        $batch = StockBatch::create([
            'item_id' => $validated['item_id'],
            'quantity' => $validated['quantity'],
            'supplier' => $validated['supplier'] ?? null,
            'date_received' => $validated['date_received'],
            'expiration_date' => $validated['expiration_date'] ?? null,
        ]);

        $inventorySync->syncItem($batch->item->fresh('stockBatches'));

        return redirect('/inventory')->with('success', 'Stock added');
    }
}