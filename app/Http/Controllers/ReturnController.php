<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\ReturnItem;
use App\Services\InventorySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function create(InventorySyncService $inventorySync)
    {
        $inventorySync->syncAllItems();

        $items = Inventory::where('type', 'Returnable')->orWhere('type', 'Consumable')->get();
        $recentReturns = ReturnItem::with('inventory')->latest()->take(5)->get();

        return view('returns.create', compact('items', 'recentReturns'));
    }

    public function store(Request $request, InventorySyncService $inventorySync)
    {
        $validated = $request->validate([
            'inventory_id' => 'required|exists:inventory,id',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|in:Good,Damaged,Missing',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated, $inventorySync) {
            ReturnItem::create($validated);

            if ($validated['condition'] === 'Good') {
                $inventory = Inventory::whereKey($validated['inventory_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $inventorySync->receiveReturn($inventory, $validated['quantity']);
            }
        });

        return back()->with('success', 'Return processed. Good items were added back to stock.');
    }
}
