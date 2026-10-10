<?php

namespace App\Http\Controllers;

use App\Models\BorrowRelease;
use App\Models\Inventory;
use App\Models\Request;
use App\Models\User;
use App\Services\InventorySyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request as HttpRequest;

class BorrowReleaseController extends Controller
{
   public function create(InventorySyncService $inventorySync)
{
    $inventorySync->syncAllItems();

    $items = Inventory::all();

    $deliveryPersonnel = User::where('role', 'delivery_personnel')
        ->orderBy('name')
        ->get();

    $approvedRequests = Request::with('inventory')
        ->where('status', 'Approved')
        ->latest()
        ->get();

    $recentReleases = BorrowRelease::with([
        'request',
        'inventory',
        'deliveryPersonnel'
    ])
        ->latest('released_at')
        ->take(5)
        ->get();

    return view(
        'borrow-release.create',
        compact(
            'items',
            'approvedRequests',
            'recentReleases',
            'deliveryPersonnel'
        )
    );
}

    public function store(
        HttpRequest $request,
        InventorySyncService $inventorySync
    ) {
        $validated = $request->validate([
    'request_id' => 'required|exists:requests,id',
    'inventory_id' => 'required|exists:inventory,id',

    'delivery_personnel_id' => [
        'required',
        'exists:users,id',
        function ($attribute, $value, $fail) {
            $user = User::find($value);

            if (!$user || $user->role !== 'delivery_personnel') {
                $fail('The selected user is not a delivery personnel.');
            }
        },
    ],

    'quantity' => 'required|integer|min:1',
    'unit' => 'required|string|max:100',
    'purpose' => 'required|string|max:255',
    'location' => 'required|string|max:255',
    'released_at' => 'required|date',
]);

        return DB::transaction(function () use ($validated, $inventorySync) {
            $supplyRequest = Request::whereKey($validated['request_id'])
                ->where('status', 'Approved')
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $supplyRequest->inventory_id !== (int) $validated['inventory_id']) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'The selected item does not match the approved request.'
                    );
            }

            if ($validated['quantity'] > $supplyRequest->quantity) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Release quantity cannot exceed the approved request quantity.'
                    );
            }

            $inventory = Inventory::whereKey($validated['inventory_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $inventory = $inventorySync->syncInventory($inventory);

            if ($inventory->quantity < $validated['quantity']) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Not enough stock available for this release.'
                    );
            }

            BorrowRelease::create([
                ...$validated,
                'delivery_status' => 'Loading',
            ]);

            $inventorySync->releaseFromInventory(
                $inventory,
                $validated['quantity']
            );

            $supplyRequest->update([
                'status' => 'Released',
                'notification_status' => 'Responder notified: items released',
            ]);

            return redirect()
                ->route('borrow-release.create')
                ->with(
                    'success',
                    'Items released, stock deducted, and request marked as released.'
                );
        });
    }
}