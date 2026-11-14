<?php

namespace App\Http\Controllers;

use App\Models\BorrowRelease;
use App\Models\Inventory;
use App\Models\Request;
use App\Models\ResqoperationForwardedRequest;
use App\Models\StockBatch;
use App\Models\StorageLocation;
use App\Models\User;
use App\Services\InventorySyncService;
use App\Services\NotificationService;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BorrowReleaseController extends Controller
{
    public function create(InventorySyncService $inventorySync, HttpRequest $httpRequest)
    {
        abort_unless(
            in_array($httpRequest->user()?->role, ['admin', 'staff'], true),
            403,
            'You do not have permission to create borrow releases.'
        );

        $inventorySync->syncAllItems();

        $deliveryPersonnel = User::where('role', 'delivery_personnel')
            ->orderBy('name')
            ->get();

        $requestsQuery = Request::with([
            'inventory.item.stockBatches',
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

        $forwardedQuery = ResqoperationForwardedRequest::with(['inventory.item.stockBatches'])
            ->withSum('borrowReleases as released_quantity', 'quantity')
            ->whereIn(DB::raw('LOWER(resqperation_status)'), ['approved', 'partially released', 'in progress', 'in_progress'])
            ->whereNotNull('inventory_id')
            ->whereRaw('COALESCE(approved_quantity, quantity) > COALESCE((SELECT SUM(borrow_releases.quantity) FROM borrow_releases WHERE borrow_releases.forwarded_request_id = resqperation_forwarded_requests.id), 0)');
        if ($search !== '') {
            $forwardedQuery->where(function ($query) use ($search): void {
                $query->where('tracking_reference', 'like', "%{$search}%")
                    ->orWhere('requested_by', 'like', "%{$search}%")
                    ->orWhereHas('inventory', fn ($inventoryQuery) => $inventoryQuery
                        ->where('sku', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%"));
            });
        }
        $approvedForwardedRequests = $forwardedQuery
            ->latest()
            ->paginate(15, ['*'], 'forwarded_release_page')
            ->withQueryString();

        $storageLocations = StorageLocation::availableInMambaling()
            ->orderBy('warehouse_name')->orderBy('name')->get();
        $locationAvailability = [];
        $releaseRows = $approvedRequests->getCollection();
        foreach ($approvedForwardedRequests as $forwardedRequest) {
            $forwardedRequest->is_forwarded_request = true;
            $forwardedRequest->request_code = $forwardedRequest->tracking_reference;
            $forwardedRequest->source = $forwardedRequest->requested_by ?? $forwardedRequest->request_source;
            $forwardedRequest->quantity = $forwardedRequest->quantity;
            $releaseRows->push($forwardedRequest);
        }
        foreach ($releaseRows as $approvedRequest) {
            $inventory = $approvedRequest->inventory;
            $item = $inventory?->item;
            $releaseKey = ! empty($approvedRequest->is_forwarded_request)
                ? 'forwarded-'.$approvedRequest->id
                : (string) $approvedRequest->id;
            foreach ($storageLocations as $storageLocation) {
                $locationAvailability[$releaseKey][$storageLocation->id] = $item
                    ? (int) $item->stockBatches->filter(fn ($batch) => (int) $batch->quantity > 0
                        && $batch->storage_location === $storageLocation->name)->sum('quantity')
                    : (($inventory?->storage_location === $storageLocation->name) ? (int) ($inventory?->quantity ?? 0) : 0);
            }
        }
        $approvedRequests->setCollection($releaseRows);

        $recentReleases = BorrowRelease::with([
            'request',
            'forwardedRequest',
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
                'approvedForwardedRequests',
                'recentReleases',
                'deliveryPersonnel',
                'search',
                'storageLocations',
                'locationAvailability',
            )
        );
    }

    public function store(
        HttpRequest $request,
        InventorySyncService $inventorySync,
        NotificationService $notifications,
    ) {
        abort_unless(
            in_array($request->user()?->role, ['admin', 'staff'], true),
            403,
            'You do not have permission to release stock.'
        );

        $validated = $request->validate([
            'request_id' => 'nullable|required_without:forwarded_request_id|exists:requests,id',
            'forwarded_request_id' => 'nullable|required_without:request_id|exists:resqperation_forwarded_requests,id',
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
            'source_storage_location' => ['required', 'integer', Rule::exists('storage_locations', 'id')->where(fn ($query) => $query->where('is_active', true)->where('warehouse_name', 'like', '%Mambaling%'))],
        ]);

        if (! empty($validated['request_id']) && ! empty($validated['forwarded_request_id'])) {
            throw ValidationException::withMessages([
                'request_id' => 'A release must be linked to exactly one request.',
            ]);
        }

        return DB::transaction(function () use ($validated, $inventorySync, $notifications, $request) {
            $sourceLocation = StorageLocation::availableInMambaling()
                ->whereKey($validated['source_storage_location'])->lockForUpdate()->first();
            if (! $sourceLocation) {
                throw ValidationException::withMessages(['source_storage_location' => 'Choose an active Mambaling storage location.']);
            }
            $isForwarded = ! empty($validated['forwarded_request_id']);
            $supplyRequest = $isForwarded
                ? null
                : Request::whereKey($validated['request_id'])->lockForUpdate()->first();
            $forwardedRequest = $isForwarded
                ? ResqoperationForwardedRequest::whereKey($validated['forwarded_request_id'])->lockForUpdate()->first()
                : null;
            if (! $supplyRequest && ! $forwardedRequest) {
                throw ValidationException::withMessages(['request_id' => 'The selected request no longer exists.']);
            }

            $previousRelease = BorrowRelease::where('idempotency_key', $validated['idempotency_key'])
                ->first();

            if ($previousRelease) {
                $sameRequest = $isForwarded
                    ? (int) $previousRelease->forwarded_request_id === (int) $forwardedRequest->id
                    : (int) $previousRelease->request_id === (int) $supplyRequest->id;
                if ($sameRequest) {
                    return redirect()
                        ->route('borrow-release.create')
                        ->with('success', 'This release was already processed.');
                }

                throw ValidationException::withMessages([
                    'idempotency_key' => 'This release form has already been submitted.',
                ]);
            }

            $requestStatus = $isForwarded
                ? strtolower((string) $forwardedRequest->resqperation_status)
                : $supplyRequest->status;
            $approvedStatuses = $isForwarded
                ? ['approved', 'partially released', 'in progress', 'in_progress']
                : ['Approved', 'Partially Released'];
            if (! in_array($requestStatus, $approvedStatuses, true)) {
                throw ValidationException::withMessages([
                    'request_id' => 'Only approved requests with an outstanding quantity can be released.',
                ]);
            }

            $releaseQuery = BorrowRelease::query()
                ->when($isForwarded, fn ($query) => $query->where('forwarded_request_id', $forwardedRequest->id))
                ->when(! $isForwarded, fn ($query) => $query->where('request_id', $supplyRequest->id));
            $releasedQuantity = (int) $releaseQuery
                ->sum('quantity');
            $approvedQuantity = (int) ($isForwarded
                ? ($forwardedRequest->approved_quantity ?? $forwardedRequest->quantity)
                : ($supplyRequest->approved_quantity ?? $supplyRequest->quantity));
            $remainingQuantity = max($approvedQuantity - $releasedQuantity, 0);

            if ($remainingQuantity === 0) {
                throw ValidationException::withMessages([
                    'request_id' => 'This request has already been fully released.',
                ]);
            }

            if ((int) $validated['quantity'] !== $remainingQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Release quantity must equal the remaining approved quantity of {$remainingQuantity}.",
                ]);
            }

            $inventoryId = $isForwarded ? $forwardedRequest->inventory_id : $supplyRequest->inventory_id;
            $inventory = Inventory::whereKey($inventoryId)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                throw ValidationException::withMessages([
                    'request_id' => 'The requested inventory item is no longer available.',
                ]);
            }

            $inventory = $inventorySync->syncInventory($inventory);

            $item = $inventory->item;
            $availableAtLocation = $item
                ? StockBatch::where('item_id', $item->id)->where('quantity', '>', 0)
                    ->where('storage_location', $sourceLocation->name)->sum('quantity')
                : ($inventory->storage_location === $sourceLocation->name ? (int) $inventory->quantity : 0);
            if ((int) $validated['quantity'] > (int) $availableAtLocation) {
                throw ValidationException::withMessages(['quantity' => 'Available stock at the selected location is lower than the requested release quantity.']);
            }

            $requestCode = $isForwarded ? $forwardedRequest->tracking_reference : $supplyRequest->request_code;
            $purpose = $isForwarded
                ? ($forwardedRequest->description ?: 'Approved request '.$requestCode)
                : ($supplyRequest->purpose ?: 'Approved request '.$requestCode);
            BorrowRelease::create([
                'request_id' => $supplyRequest?->id,
                'forwarded_request_id' => $forwardedRequest?->id,
                'inventory_id' => $inventory->id,
                'delivery_personnel_id' => $validated['delivery_personnel_id'],
                'processed_by_user_id' => auth()->id(),
                'idempotency_key' => $validated['idempotency_key'],
                'unit' => $item?->unit_type ?: 'Unit',
                'quantity' => $validated['quantity'],
                'purpose' => $purpose,
                'location' => $validated['location'],
                'source_storage_location' => $sourceLocation->name,
                'released_at' => now(),
                'delivery_status' => 'Loading',
            ]);

            $inventorySync->releaseFromInventory(
                $inventory,
                $validated['quantity'],
                $sourceLocation->name
            );

            $isComplete = $releasedQuantity + (int) $validated['quantity'] >= $approvedQuantity;
            if ($isForwarded) {
                $forwardedRequest->update([
                    'resqperation_status' => $isComplete ? 'released' : 'partially released',
                ]);
            } else {
                $supplyRequest->update([
                    'status' => $isComplete ? 'Released' : 'Partially Released',
                    'notification_status' => 'Responder notified: items released',
                ]);
            }

            $notifications->notifyRoles(
                ['admin', 'staff'],
                'Items released',
                "Items for request {$requestCode} were released to delivery.",
                route('delivery-monitoring.index', [], false),
                'items_released',
                $requestCode,
                $inventory->name,
            );

            return redirect()
                ->route('borrow-release.create')
                ->with(
                    'success',
                    'Release recorded and inventory updated.'
                );
        });
    }

    public function monitoring(HttpRequest $httpRequest)
    {
        abort_unless(
        in_array($httpRequest->user()?->role, ['super_admin','admin', 'staff'], true),
        403,
        'You do not have permission to monitor deliveries.'
    );

    $deliveries = BorrowRelease::with([
            'request',
            'forwardedRequest',
            'inventory',
            'deliveryPersonnel',
        ])
            ->latest('released_at')
            ->get();

        return view('delivery-monitoring.index', compact('deliveries'));
    }
}
