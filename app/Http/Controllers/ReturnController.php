<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\BorrowRelease;
use App\Models\ReturnItem;
use App\Services\InventorySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnController extends Controller
{
    public function create(InventorySyncService $inventorySync)
    {
        $inventorySync->syncAllItems();

        $returnableReleases = BorrowRelease::with([
            'inventory.item',
            'request',
            'deliveryPersonnel',
            'returns',
        ])
            ->withSum('returns as returned_quantity', 'quantity')
            ->whereHas('inventory', fn ($query) => $query->where('type', 'Returnable'))
            ->whereRaw('borrow_releases.quantity > COALESCE((SELECT SUM(returns.quantity) FROM returns WHERE returns.borrow_release_id = borrow_releases.id), 0)')
            ->latest('released_at')
            ->paginate(15)
            ->withQueryString();

        $recentReturns = ReturnItem::with(['inventory', 'borrowRelease.request', 'processedBy'])
            ->latest()
            ->take(5)
            ->get();

        return view('returns.create', compact('returnableReleases', 'recentReturns'));
    }

    public function store(Request $request, InventorySyncService $inventorySync)
    {
        $validated = $request->validate([
            'borrow_release_id' => 'required|exists:borrow_releases,id',
            'idempotency_key' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|in:Good,Damaged,Missing',
            'notes' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($validated, $inventorySync, $request): void {
            $previousReturn = ReturnItem::where('idempotency_key', $validated['idempotency_key'])->first();

            if ($previousReturn) {
                if ((int) $previousReturn->borrow_release_id === (int) $validated['borrow_release_id']) {
                    return;
                }

                throw ValidationException::withMessages([
                    'idempotency_key' => 'This return form has already been submitted.',
                ]);
            }

            $release = BorrowRelease::whereKey($validated['borrow_release_id'])
                ->lockForUpdate()
                ->firstOrFail();
            $inventory = Inventory::whereKey($release->inventory_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($inventory->type !== 'Returnable') {
                throw ValidationException::withMessages([
                    'borrow_release_id' => 'Only released reusable items can be returned.',
                ]);
            }

            $returnedQuantity = (int) ReturnItem::where('borrow_release_id', $release->id)->sum('quantity');
            $outstandingQuantity = max((int) $release->quantity - $returnedQuantity, 0);

            if ($outstandingQuantity === 0) {
                throw ValidationException::withMessages([
                    'borrow_release_id' => 'This release has no quantity outstanding to return.',
                ]);
            }

            if ((int) $validated['quantity'] > $outstandingQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Return quantity exceeds the outstanding quantity of {$outstandingQuantity}.",
                ]);
            }

            ReturnItem::create([
                ...$validated,
                'inventory_id' => $inventory->id,
                'processed_by_user_id' => $request->user()->id,
            ]);

            if ($validated['condition'] === 'Good') {
                $inventorySync->receiveReturn($inventory, $validated['quantity']);
            }
        });

        return back()->with('success', 'Return recorded. Only items in Good condition were added back to available stock.');
    }
}
