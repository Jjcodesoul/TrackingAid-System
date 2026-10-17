<?php

namespace App\Http\Controllers;

use App\Models\BorrowRelease;
use App\Models\Inventory;
use App\Models\Request;
use App\Models\User;
use App\Services\InventorySyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\ValidationException;

class BorrowReleaseController extends Controller
{
    public function create(InventorySyncService $inventorySync, HttpRequest $httpRequest)
    {
        $inventorySync->syncAllItems();

        $deliveryPersonnel = User::where('role', 'delivery_personnel')
            ->orderBy('name')
            ->get();

        $requestsQuery = Request::with([
            'inventory.item',
            'borrowReleases.deliveryPersonnel',
        ])
            ->withSum('borrowReleases as released_quantity', 'quantity')
            ->whereIn('status', ['Approved', 'Partially Released'])
            ->whereHas('inventory')
            ->whereRaw('COALESCE(requests.approved_quantity, requests.quantity) > COALESCE((SELECT SUM(borrow_releases.quantity) FROM borrow_releases WHERE borrow_releases.request_id = requests.id), 0)');

        $search = trim((string) $httpRequest->query('search', ''));

        if ($search !== '') {
            $requestsQuery->where(function ($query) use ($search) {
                $query->where('request_code', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('responder_email', 'like', "%{$search}%")
                    ->orWhereHas('inventory', function ($inventoryQuery) use ($search) {
                        $inventoryQuery->where('sku', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        $approvedRequests = $requestsQuery
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $recentReleases = BorrowRelease::with([
            'request',
            'inventory',
            'deliveryPersonnel',
            'processedBy',
        ])
            ->latest('released_at')
            ->take(5)
            ->get();

        return view(
            'borrow-release.create',
            compact(
                'approvedRequests',
                'recentReleases',
                'deliveryPersonnel',
                'search'
            )
        );
    }

    public function store(
        HttpRequest $request,
        InventorySyncService $inventorySync
    ) {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);

        $validated = $request->validate([
            'request_id' => 'required|exists:requests,id',
            'idempotency_key' => 'required|uuid',
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
            'location' => 'required|string|max:255',
        ]);

        return DB::transaction(function () use ($validated, $inventorySync) {
            $supplyRequest = Request::whereKey($validated['request_id'])
                ->lockForUpdate()
                ->first();

            if (! $supplyRequest) {
                throw ValidationException::withMessages([
                    'request_id' => 'The selected request no longer exists.',
                ]);
            }

            $previousRelease = BorrowRelease::where('idempotency_key', $validated['idempotency_key'])
                ->first();

            if ($previousRelease) {
                if ((int) $previousRelease->request_id === (int) $supplyRequest->id) {
                    return redirect()
                        ->route('borrow-release.create')
                        ->with('success', 'This release was already processed.');
                }

                throw ValidationException::withMessages([
                    'idempotency_key' => 'This release form has already been submitted.',
                ]);
            }

            if (! in_array($supplyRequest->status, ['Approved', 'Partially Released'], true)) {
                throw ValidationException::withMessages([
                    'request_id' => 'Only approved requests with an outstanding quantity can be released.',
                ]);
            }

            $releasedQuantity = (int) BorrowRelease::where('request_id', $supplyRequest->id)
                ->sum('quantity');
            $approvedQuantity = (int) ($supplyRequest->approved_quantity ?? $supplyRequest->quantity);
            $remainingQuantity = max($approvedQuantity - $releasedQuantity, 0);

            if ($remainingQuantity === 0) {
                $supplyRequest->update(['status' => 'Released']);

                throw ValidationException::withMessages([
                    'request_id' => 'This request has already been fully released.',
                ]);
            }

            if ((int) $validated['quantity'] !== $remainingQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Release quantity must equal the remaining approved quantity of {$remainingQuantity}.",
                ]);
            }

            $inventory = Inventory::whereKey($supplyRequest->inventory_id)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                throw ValidationException::withMessages([
                    'request_id' => 'The requested inventory item is no longer available.',
                ]);
            }

            $inventory = $inventorySync->syncInventory($inventory);

            if ((int) $validated['quantity'] > (int) $inventory->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Available stock is lower than the requested release quantity.',
                ]);
            }

            $item = $inventory->item;

            BorrowRelease::create([
                'request_id' => $supplyRequest->id,
                'inventory_id' => $inventory->id,
                'delivery_personnel_id' => $validated['delivery_personnel_id'],
                'processed_by_user_id' => auth()->id(),
                'idempotency_key' => $validated['idempotency_key'],
                'unit' => $item?->unit_type ?: 'Unit',
                'quantity' => $validated['quantity'],
                'purpose' => $supplyRequest->purpose ?: 'Approved request '.$supplyRequest->request_code,
                'location' => $validated['location'],
                'released_at' => now(),
                'delivery_status' => 'Loading',
            ]);

            $inventorySync->releaseFromInventory(
                $inventory,
                $validated['quantity']
            );

            $supplyRequest->update([
                'status' => $releasedQuantity + (int) $validated['quantity'] >= $approvedQuantity
                    ? 'Released'
                    : 'Partially Released',
                'notification_status' => 'Responder notified: items released',
            ]);

            return redirect()
                ->route('borrow-release.create')
                ->with(
                    'success',
                    'Release recorded and inventory updated.'
                );
        });
    }

    public function monitoring()
    {
        $deliveries = BorrowRelease::with([
            'request',
            'inventory',
            'deliveryPersonnel',
        ])
            ->latest('released_at')
            ->get();

        return view('delivery-monitoring.index', compact('deliveries'));
    }
}
