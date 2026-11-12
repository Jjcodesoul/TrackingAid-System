<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\StorageLocation;
use App\Services\InventorySyncService;
use Illuminate\Support\Str;

class StockController extends Controller
{
    public function create()
    {
        $storageLocation = StorageLocation::designatedMambaling()->where('is_active', true)->firstOrFail();
        $items = Item::with('stockBatches')
            ->get()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $categories = $items->pluck('category')->unique()->sort()->values();

        $submissionKey = (string) Str::uuid();

        return view('stock.create', compact('items', 'categories', 'storageLocation', 'submissionKey'));
    }

    public function store(Request $request, InventorySyncService $inventorySync)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'submission_key' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'supplier' => 'nullable|string|max:255',
            'date_received' => 'required|date',
            'expiration_date' => 'nullable|date',
        ]);

        $inventorySync->receiveStock(
            (int) $validated['item_id'],
            (int) $validated['quantity'],
            (int) $request->user()->id,
            $validated['date_received'],
            $validated['expiration_date'] ?? null,
            $validated['supplier'] ?? null,
            $validated['submission_key'],
        );

        return redirect('/inventory')->with('success', 'Stock added');
    }
}
