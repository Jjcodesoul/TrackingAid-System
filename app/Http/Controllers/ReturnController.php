<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\ReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function create()
    {
        $items = Inventory::where('type', 'Returnable')->orWhere('type', 'Consumable')->get();

        return view('returns.create', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_id' => 'required|exists:inventory,id',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|in:Good,Damaged,Missing',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated) {
            ReturnItem::create($validated);

            if ($validated['condition'] === 'Good') {
                Inventory::whereKey($validated['inventory_id'])
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->increment('quantity', $validated['quantity']);
            }
        });

        return back()->with('success', 'Return processed. Good items were added back to stock.');
    }
}
