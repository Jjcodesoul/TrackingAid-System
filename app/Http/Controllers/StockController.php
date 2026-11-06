<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\StockBatch;
use App\Models\StorageLocation;
use App\Services\InventorySyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    public function create()
    {
        $storageLocations = StorageLocation::availableInMambaling()
            ->orderBy('warehouse_name')->orderBy('name')->get();
        $items = Item::with('stockBatches')
            ->get()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $categories = $items->pluck('category')->unique()->sort()->values();

        return view('stock.create', compact('items', 'categories', 'storageLocations'));
    }

    public function store(Request $request, InventorySyncService $inventorySync)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'storage_location_id' => ['required', 'integer', Rule::exists('storage_locations', 'id')->where(fn ($query) => $query->where('is_active', true)->where('warehouse_name', 'like', '%Mambaling%'))],
            'quantity' => 'required|integer|min:1',
            'supplier' => 'nullable|string|max:255',
            'date_received' => 'required|date',
            'expiration_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($validated, $request, $inventorySync) {
            $item = Item::whereKey($validated['item_id'])->lockForUpdate()->firstOrFail();
            $location = StorageLocation::availableInMambaling()->whereKey($validated['storage_location_id'])
                ->lockForUpdate()->first();
            if (! $location) {
                throw ValidationException::withMessages(['storage_location_id' => 'Select an active verified Mambaling storage location.']);
            }
            $batch = StockBatch::create([
                'item_id' => $item->id,
                'quantity' => $validated['quantity'],
                'supplier' => $validated['supplier'] ?? null,
                'date_received' => $validated['date_received'],
                'expiration_date' => $validated['expiration_date'] ?? null,
                'created_by_user_id' => $request->user()->id,
                'movement_type' => 'stock-in',
                'storage_location' => $location->name,
            ]);
            $inventorySync->syncItem($item->fresh('stockBatches'));
        });

        return redirect('/inventory')->with('success', 'Stock added');
    }
}
