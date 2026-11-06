<?php

namespace App\Http\Controllers;

use App\Models\BorrowRelease;
use App\Models\Inventory;
use App\Models\ReturnItem;
use App\Models\StorageLocation;
use App\Services\InventorySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnController extends Controller
{
    public function create(InventorySyncService $inventorySync)
    {
        $inventorySync->syncAllItems();

        $returnableReleases = BorrowRelease::query()
            ->with(['request', 'inventory.item'])
            ->withSum('returns as returned_quantity', 'quantity')
            ->whereHas('inventory', fn ($query) => $query->where('type', 'Returnable'))
            ->whereRaw(
                'borrow_releases.quantity > (select coalesce(sum(returns.quantity), 0) from returns where returns.borrow_release_id = borrow_releases.id)'
            )
            ->latest('released_at')
            ->paginate(15);
        $verifiedLocationNames = StorageLocation::availableInMambaling()->pluck('name');
        $recentReturns = ReturnItem::with(['inventory', 'borrowRelease.request', 'processedBy', 'receivedBy'])
            ->latest()
            ->take(5)
            ->get();

        return view('returns.create', compact('returnableReleases', 'recentReturns', 'verifiedLocationNames'));
    }

    public function store(Request $request, InventorySyncService $inventorySync)
    {
        $validated = $request->validate([
            'borrow_release_id' => 'required|exists:borrow_releases,id',
            'idempotency_key' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|in:Good,Damaged,Missing',
            'returned_by_name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $wasCreated = DB::transaction(function () use ($validated, $inventorySync, $request): bool {
            $release = BorrowRelease::query()
                ->whereKey($validated['borrow_release_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $previousReturn = ReturnItem::where('idempotency_key', $validated['idempotency_key'])->first();

            if ($previousReturn) {
                if ((int) $previousReturn->borrow_release_id === (int) $release->id) {
                    return false;
                }

                throw ValidationException::withMessages([
                    'idempotency_key' => 'This return form has already been submitted.',
                ]);
            }

            $inventory = Inventory::whereKey($release->inventory_id)
                ->lockForUpdate()
                ->first();

            if (! $inventory || $inventory->type !== 'Returnable') {
                throw ValidationException::withMessages([
                    'borrow_release_id' => 'Returns can only be recorded for released reusable items.',
                ]);
            }

            $returnLocationName = $release->source_storage_location;
            $returnLocation = StorageLocation::availableInMambaling()
                ->where('name', $returnLocationName)
                ->lockForUpdate()
                ->first();
            if (! $returnLocation) {
                throw ValidationException::withMessages([
                    'borrow_release_id' => 'The release storage location is not active and verified in Mambaling. Ask an administrator to verify it before recording this return.',
                ]);
            }

            $returnedQuantity = (int) ReturnItem::where('borrow_release_id', $release->id)->sum('quantity');
            $outstandingQuantity = max((int) $release->quantity - $returnedQuantity, 0);

            if ((int) $validated['quantity'] > $outstandingQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Return quantity cannot exceed the outstanding quantity of {$outstandingQuantity}.",
                ]);
            }

            ReturnItem::create([
                'inventory_id' => $inventory->id,
                'borrow_release_id' => $release->id,
                'processed_by_user_id' => $request->user()->id,
                'returned_by_name' => $validated['returned_by_name'],
                'received_by_user_id' => $request->user()->id,
                'idempotency_key' => $validated['idempotency_key'],
                'quantity' => $validated['quantity'],
                'condition' => $validated['condition'],
                'storage_location' => $returnLocation->name,
                'returned_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($validated['condition'] === 'Good') {
                $inventorySync->receiveReturn($inventory, $validated['quantity'], $request->user()->id, $returnLocation->name);
            }

            return true;
        });

        return back()->with(
            'success',
            $wasCreated
                ? 'Return processed. Good items were added back to stock.'
                : 'This return was already processed.'
        );
    }
}
