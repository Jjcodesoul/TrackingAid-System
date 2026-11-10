<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\StockBatch;
use App\Models\StorageLocation;
use App\Services\InventorySyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

        DB::transaction(function () use ($validated, $request, $inventorySync) {
            $item = Item::whereKey($validated['item_id'])->lockForUpdate()->firstOrFail();
            $priorBatch = StockBatch::where('submission_key', $validated['submission_key'])->first();
            if ($priorBatch) {
                if ((int) $priorBatch->item_id !== (int) $validated['item_id']
                    || (int) $priorBatch->quantity !== (int) $validated['quantity']) {
                    throw ValidationException::withMessages(['submission_key' => 'This receipt has already been submitted with different details.']);
                }

                return;
            }

            $location = StorageLocation::designatedMambaling()->where('is_active', true)
                ->lockForUpdate()->first();
            if (! $location) {
                throw ValidationException::withMessages(['storage_location_id' => 'Mambaling Storage is not configured as the active storage location.']);
            }
            $batch = StockBatch::create([
                'item_id' => $item->id,
                'quantity' => $validated['quantity'],
                'supplier' => $validated['supplier'] ?? null,
                'date_received' => $validated['date_received'],
                'expiration_date' => $validated['expiration_date'] ?? null,
                'created_by_user_id' => $request->user()->id,
                'movement_type' => 'stock-in',
                'submission_key' => $validated['submission_key'],
                'storage_location' => $location->name,
            ]);
            $inventorySync->syncItem($item->fresh('stockBatches'));
        });

        return redirect('/inventory')->with('success', 'Stock added');
    }
}
