<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\StockBatch;
use App\Models\StorageLocation;
use App\Services\InventorySyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

        DB::transaction(function () use ($validated, $request, $inventorySync) {
            $item = Item::whereKey($validated['item_id'])->lockForUpdate()->firstOrFail();
            if (! StorageLocation::where('name', $item->storage_location)->where('is_active', true)->exists()) {
                throw ValidationException::withMessages(['item_id' => 'This item has an unverified storage location. Verify or update its location before recording stock-in.']);
            }
            $batch = StockBatch::create([
                'item_id' => $item->id,
                'quantity' => $validated['quantity'],
                'supplier' => $validated['supplier'] ?? null,
                'date_received' => $validated['date_received'],
                'expiration_date' => $validated['expiration_date'] ?? null,
                'created_by_user_id' => $request->user()->id,
                'movement_type' => 'stock-in',
                'storage_location' => $item->storage_location,
            ]);
            $inventorySync->syncItem($item->fresh('stockBatches'));
        });

        return redirect('/inventory')->with('success', 'Stock added');
    }
}
