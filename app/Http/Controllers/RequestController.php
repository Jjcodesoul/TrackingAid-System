<?php

namespace App\Http\Controllers;

use App\Models\Request as SupplyRequest;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\ResqoperationForwardedRequest;
use App\Services\InventorySyncService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestController extends Controller
{
    public function index(InventorySyncService $inventorySync, Request $httpRequest)
    {
        $inventorySync->syncAllItems();

        $search = trim((string) $httpRequest->query('search', ''));
        $status = (string) $httpRequest->query('status', 'all');
        $allowedStatuses = ['Pending', 'Approved', 'Partially Released', 'Released', 'Rejected'];
        abort_unless($status === 'all' || in_array($status, $allowedStatuses, true), 404);

        $requestsQuery = SupplyRequest::with(['inventory.item', 'approvedBy'])->latest();
        if ($search !== '') {
            $requestsQuery->where(function ($query) use ($search): void {
                $query->where('request_code', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('responder_email', 'like', "%{$search}%")
                    ->orWhereHas('inventory', fn ($inventory) => $inventory->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }
        if ($status !== 'all') $requestsQuery->where('status', $status);
        $requests = $requestsQuery->paginate(15, ['*'], 'request_page')->withQueryString();

        $stockAvailability = $this->stockAvailability($requests);

        // 2. Fetch external forwarded requests sent by ResQOperation
        $forwardedQuery = ResqoperationForwardedRequest::latest();
        if ($search !== '') {
            $forwardedQuery->where(function ($query) use ($search): void {
                $query->where('tracking_reference', 'like', "%{$search}%")
                    ->orWhere('resqperation_request_id', 'like', "%{$search}%")
                    ->orWhere('requested_by', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%")
                    ->orWhere('resource_type', 'like', "%{$search}%");
            });
        }
        if ($status !== 'all') {
            $statusValue = strtolower($status);
            if ($statusValue === 'pending') {
                $forwardedQuery->whereRaw("LOWER(COALESCE(resqperation_status, 'forwarded')) IN (?, ?)", ['pending', 'forwarded']);
            } elseif ($statusValue === 'partially released') {
                $forwardedQuery->whereRaw("LOWER(COALESCE(resqperation_status, 'forwarded')) IN (?, ?, ?)", ['partially released', 'in progress', 'in_progress']);
            } else {
                $forwardedQuery->whereRaw('LOWER(COALESCE(resqperation_status, ?)) = ?', ['forwarded', $statusValue]);
            }
        }
        $forwardedRequests = $forwardedQuery->paginate(15, ['*'], 'forwarded_page')->withQueryString();
        $forwardedAvailability = $forwardedRequests->mapWithKeys(fn (ResqoperationForwardedRequest $forwardedRequest): array => [
            $forwardedRequest->id => $this->externalStockAvailability($forwardedRequest->item_name ?: $forwardedRequest->resource_type, (int) $forwardedRequest->quantity),
        ]);

        $forwardedStats = ResqoperationForwardedRequest::query()
            ->selectRaw("LOWER(COALESCE(resqperation_status, 'forwarded')) as status_key, COUNT(*) as total")
            ->groupBy('status_key')
            ->pluck('total', 'status_key');

        $stats = [
            'pending'  => SupplyRequest::where('status', 'Pending')->count() + $forwardedStats->except(['approved', 'rejected', 'released', 'partially released', 'in progress', 'in_progress'])->sum(),
            'approved' => SupplyRequest::where('status', 'Approved')->count() + $forwardedStats->get('approved', 0),
            'released' => SupplyRequest::whereIn('status', ['Released', 'Partially Released'])->count() + $forwardedStats->only(['released', 'partially released', 'in progress', 'in_progress'])->sum(),
            'rejected' => SupplyRequest::where('status', 'Rejected')->count() + $forwardedStats->get('rejected', 0),
        ];

        // 4. Pass all variables to view
        return view('requests.index', compact('requests', 'forwardedRequests', 'stats', 'stockAvailability', 'forwardedAvailability', 'search', 'status'));
    }

    public function approve(Request $httpRequest, SupplyRequest $request, InventorySyncService $inventorySync, NotificationService $notifications)
    {
        abort_unless(
            in_array($httpRequest->user()?->role, ['admin', 'staff'], true),
            403,
            'You do not have permission to approve requests.'
    );

        $validated = $httpRequest->validate([
            'approved_quantity' => ['required', 'integer', 'min:1'],
            'approval_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $request, $inventorySync): void {
            $supplyRequest = SupplyRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($supplyRequest->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'request_id' => 'Only pending requests can be approved.',
                ]);
            }

            $approvedQuantity = (int) $validated['approved_quantity'];

            if ($approvedQuantity > (int) $supplyRequest->quantity) {
                throw ValidationException::withMessages([
                    'approved_quantity' => 'Approved quantity cannot exceed the original requested quantity.',
                ]);
            }

            if ($approvedQuantity < (int) $supplyRequest->quantity && blank($validated['approval_notes'] ?? null)) {
                throw ValidationException::withMessages([
                    'approval_notes' => 'Explain why the approved quantity is lower than requested.',
                ]);
            }

            $inventory = Inventory::whereKey($supplyRequest->inventory_id)->lockForUpdate()->first();

            if (! $inventory) {
                throw ValidationException::withMessages([
                    'request_id' => 'The requested inventory item is no longer available.',
                ]);
            }

            $inventory = $inventorySync->syncInventory($inventory);
            $availableQuantity = $this->availableToAllocate($inventory, (int) $supplyRequest->id);

            if ($approvedQuantity > $availableQuantity) {
                $shortage = $approvedQuantity - $availableQuantity;
                throw ValidationException::withMessages([
                    'approved_quantity' => "Requested {$supplyRequest->quantity}; only {$availableQuantity} available for approval; shortage {$shortage}. Reduce the approved quantity with a reason or restock first.",
                ]);
            }

            $supplyRequest->update([
                'status' => 'Approved',
                'approved_quantity' => $approvedQuantity,
                'approved_by_user_id' => auth()->id(),
                'approval_notes' => $validated['approval_notes'] ?? null,
                'notification_status' => 'Request approved in TrackingAid',
                'approved_at' => now(),
                'rejected_at' => null,
            ]);
        });

        $notifications->notifyRoles(
            ['admin', 'staff'],
            'Request approved',
            "Request {$request->request_code} was approved and is ready for release.",
            route('borrow-release.create', [], false),
            'request_approved',
            $request->request_code,
            $request->inventory?->name,
        );

        return redirect()
            ->route('borrow-release.create')
            ->with('success', 'Request approved and stock reserved. It is ready for release review.');
    }

    public function reject(Request $httpRequest, SupplyRequest $request, NotificationService $notifications)
    {
        abort_unless(
            in_array($httpRequest->user()?->role, ['admin', 'staff'], true),
            403,
            'You do not have permission to reject requests.'

        );
        
        $validated = $httpRequest->validate([
            'approval_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $supplyRequest = SupplyRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($supplyRequest->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'request_id' => 'Only pending requests can be rejected.',
                ]);
            }

            $supplyRequest->update([
                'status' => 'Rejected',
                'notification_status' => 'Request rejected in TrackingAid',
                'rejected_at' => now(),
                'rejected_by_user_id' => auth()->id(),
                'approval_notes' => $validated['approval_notes'] ?? null,
                'approved_at' => null,
                'approved_by_user_id' => null,
            ]);
        });

        $notifications->notifyRoles(
            ['admin', 'staff'],
            'Request rejected',
            "Request {$request->request_code} was rejected.",
            route('requests.index', [], false),
            'request_rejected',
            $request->request_code,
            $request->inventory?->name,
        );

        return back()->with('success', 'Request rejected.');
    }

    
    public function approveForwarded(
        Request $httpRequest,
        ResqoperationForwardedRequest $forwardedRequest,
        InventorySyncService $inventorySync,
        NotificationService $notifications,
    ) {
        abort_unless(
            in_array($httpRequest->user()?->role, ['admin', 'staff'], true),
            403,
            'You do not have permission to approve forwarded requests.'
        );

        $approvedQuantity = DB::transaction(function () use (
            $forwardedRequest,
            $inventorySync,
            $httpRequest
        ): int {
            $lockedRequest = ResqoperationForwardedRequest::whereKey(
                $forwardedRequest->id
            )->lockForUpdate()->firstOrFail();

            if (! in_array(
                strtolower($lockedRequest->resqperation_status ?? 'forwarded'),
                ['forwarded', 'pending'],
                true
            )) {
                return 0;
            }

            $inventory = $this->matchForwardedInventory(
                $lockedRequest->item_name ?: $lockedRequest->resource_type,
                $inventorySync
            );

            if (! $inventory) {
                throw ValidationException::withMessages([
                    'request_id' => 'This request cannot be approved because its item could not be matched to one unambiguous inventory item. Verify its item name or SKU first.',
                ]);
            }

            $inventory = Inventory::whereKey($inventory->id)
                ->lockForUpdate()
                ->firstOrFail();

            $inventory = $inventorySync->syncInventory($inventory);

            $requestedQuantity = (int) $lockedRequest->quantity;

            if ($requestedQuantity <= 0) {
                throw ValidationException::withMessages([
                    'request_id' => 'The requested quantity must be greater than zero.',
                ]);
            }

            $availableQuantity = $this->availableToAllocateForForwarded(
                $inventory,
                (int) $lockedRequest->id
            );

            if ($availableQuantity <= 0) {
                throw ValidationException::withMessages([
                    'request_id' => 'No stock is currently available for this request.',
                ]);
            }

            // Approve only the available quantity.
            $approvedQuantity = min($requestedQuantity, $availableQuantity);

            $lockedRequest->inventory_id = $inventory->id;
            $lockedRequest->approved_quantity = $approvedQuantity;
            $lockedRequest->approved_by_user_id = $httpRequest->user()->id;
            $lockedRequest->approved_at = now();
            $lockedRequest->rejected_by_user_id = null;
            $lockedRequest->rejected_at = null;
            $lockedRequest->resqperation_status = 'approved';
            $lockedRequest->save();

            return $approvedQuantity;
        });

        if ($approvedQuantity === 0) {
            return back()->with('error', 'This forwarded request has already been reviewed.');
        }

        $requestedQuantity = (int) $forwardedRequest->quantity;
        $isPartial = $approvedQuantity < $requestedQuantity;

        $notifications->notifyRoles(
            ['admin', 'staff'],
            $isPartial ? 'Forwarded request partially approved' : 'Forwarded request approved',
            "Forwarded request {$forwardedRequest->tracking_reference}: {$approvedQuantity} of {$requestedQuantity} units approved.",
            route('borrow-release.create', [], false),
            'request_approved',
            $forwardedRequest->tracking_reference,
            $forwardedRequest->item_name ?: $forwardedRequest->resource_type,
        );

        return back()->with(
            'success',
            $isPartial
                ? "Request partially approved: {$approvedQuantity} of {$requestedQuantity} units approved."
                : "Forwarded request approved: {$approvedQuantity} units. It is ready for release."
        );
    }


        public function rejectForwarded(Request $httpRequest, ResqoperationForwardedRequest $forwardedRequest, NotificationService $notifications)
    {
        abort_unless(
            in_array($httpRequest->user()?->role, ['admin', 'staff'], true),
            403,
            'You do not have permission to reject forwarded requests.'
        );

        $updated = DB::transaction(function () use ($forwardedRequest): bool {
            $lockedRequest = ResqoperationForwardedRequest::whereKey($forwardedRequest->id)->lockForUpdate()->firstOrFail();
            if (! in_array(strtolower($lockedRequest->resqperation_status ?? 'forwarded'), ['forwarded', 'pending'], true)) {
                return false;
            }

            $lockedRequest->rejected_by_user_id = auth()->id();
            $lockedRequest->rejected_at = now();
            $lockedRequest->resqperation_status = 'rejected';
            $lockedRequest->save();
            return true;
        });

        if (! $updated) {
            return back()->with('error', 'This forwarded request has already been reviewed.');
        }

        $notifications->notifyRoles(
            ['admin', 'staff'],
            'Forwarded request rejected',
            "Forwarded request {$forwardedRequest->tracking_reference} was rejected.",
            route('requests.index', [], false),
            'request_rejected',
            $forwardedRequest->tracking_reference,
            $forwardedRequest->item_name ?: $forwardedRequest->resource_type,
        );

        return back()->with('success', 'Forwarded request marked rejected in TrackingAid. No external status sync is configured.');
    }

    public function storeResqData(Request $request, InventorySyncService $inventorySync, NotificationService $notifications)
    {
        if (! $request->filled('resqperation_request_id') && $request->filled('resqoperation_request_id')) {
            $request->merge([
                'resqperation_request_id' => $request->input('resqoperation_request_id'),
            ]);
        }

        $validated = $request->validate([
            'tracking_reference'       => 'required|string|max:120',
            'resqperation_request_id'  => 'required|string|max:120',
            'source_reference'         => 'nullable|string|max:120',
            'request_source'           => 'nullable|string|max:80',
            'source_system'            => 'nullable|string|max:80',
            'request_category'         => 'nullable|string|max:80',
            'resource_type'            => 'nullable|string|max:150',
            'item_name'                => 'nullable|string|max:150',
            'quantity'                 => 'sometimes|integer|min:1',
            'unit'                     => 'nullable|string|max:50',
            'urgency'                  => 'nullable|string|max:80',
            'area_label'               => 'nullable|string|max:255',
            'area_note'                => 'nullable|string',
            'requested_by'             => 'nullable|string|max:255',
            'description'              => 'nullable|string',
            'validation_notes'          => 'nullable|string',
            'validated_by_user_id'     => 'nullable|string|max:120',
            'forwarded_by_user_id'     => 'nullable|string|max:120',
            'forwarded_by_name'        => 'nullable|string|max:255',
            'forwarded_by_role'        => 'nullable|string|max:80',
            'payload_json'             => 'nullable|array',
            'forwarded_at'             => 'nullable|date',
        ]);

        $attributes = $validated;
        if (! isset($attributes['inventory_id'])) {
            $inventory = $this->matchForwardedInventory(
                $validated['item_name'] ?? $validated['resource_type'] ?? null,
                $inventorySync,
            );
            if ($inventory) {
                $attributes['inventory_id'] = $inventory->id;
            }
        }

        $forwardedRequest = ResqoperationForwardedRequest::updateOrCreate(
            ['tracking_reference' => $validated['tracking_reference']],
            $attributes
        );

        if ($forwardedRequest->wasRecentlyCreated) {
            $notifications->notifyRoles(
                ['admin'],
                'New ResQperation request',
                "Request {$forwardedRequest->tracking_reference} was received and is awaiting review.",
                route('requests.index', [], false),
                'request_received',
                $forwardedRequest->tracking_reference,
                $forwardedRequest->item_name ?: $forwardedRequest->resource_type,
            );
        }

        $stockAvailability = $this->externalStockAvailability(
            $validated['item_name'] ?? $validated['resource_type'] ?? null,
            isset($validated['quantity']) ? (int) $validated['quantity'] : null,
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Forwarded request successfully received by TrackingAid!',
            'data'    => $forwardedRequest,
            'stock_availability' => $stockAvailability,
        ], $forwardedRequest->wasRecentlyCreated ? 201 : 200);
    }

    private function matchForwardedInventory(?string $reference, InventorySyncService $inventorySync): ?Inventory
    {
        $reference = trim((string) $reference);
        if ($reference === '') {
            return null;
        }

        $item = Item::whereRaw('LOWER(sku) = ?', [mb_strtolower($reference)])->first();
        if (! $item) {
            $matches = Item::whereRaw('LOWER(name) = ?', [mb_strtolower($reference)])->limit(2)->get();
            if ($matches->count() > 1) {
                return null;
            }
            $item = $matches->first();
        }
        if ($item) {
            return $inventorySync->syncItem($item->load('stockBatches'));
        }

        $inventory = Inventory::whereRaw('LOWER(sku) = ?', [mb_strtolower($reference)])->first();
        if ($inventory) {
            return $inventory;
        }

        $matches = Inventory::whereRaw('LOWER(name) = ?', [mb_strtolower($reference)])->limit(2)->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }


    public function mobileDeliveries()
{
    $deliveries = SupplyRequest::with('inventory')
        ->whereIn('status', ['Approved', 'Released'])
        ->latest()
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $deliveries
    ]);
}

    private function stockAvailability($requests): array
    {
        $inventoryIds = $requests->pluck('inventory_id')->filter()->unique();
        $onHand = Inventory::whereIn('id', $inventoryIds)->pluck('quantity', 'id');
        $activeRequests = SupplyRequest::whereIn('status', ['Approved', 'Partially Released'])
            ->whereIn('inventory_id', $inventoryIds)
            ->withSum('borrowReleases as released_quantity', 'quantity')
            ->get(['id', 'inventory_id', 'quantity', 'approved_quantity']);

        $reserved = $activeRequests
            ->groupBy('inventory_id')
            ->map(fn ($records): int => $records->sum(fn (SupplyRequest $approvedRequest): int => max(
                (int) ($approvedRequest->approved_quantity ?? $approvedRequest->quantity)
                    - (int) ($approvedRequest->released_quantity ?? 0),
                0
            )));

        return $inventoryIds->mapWithKeys(function ($inventoryId) use ($onHand, $reserved): array {
            $physical = max((int) $onHand->get($inventoryId, 0), 0);
            $committed = (int) $reserved->get($inventoryId, 0);

            return [$inventoryId => [
                'on_hand' => $physical,
                'reserved' => $committed,
                'available' => max($physical - $committed, 0),
            ]];
        })->all();
    }

    private function availableToAllocate(Inventory $inventory, int $exceptRequestId): int
    {
        $reserved = SupplyRequest::where('inventory_id', $inventory->id)
            ->whereKeyNot($exceptRequestId)
            ->whereIn('status', ['Approved', 'Partially Released'])
            ->withSum('borrowReleases as released_quantity', 'quantity')
            ->get(['id', 'quantity', 'approved_quantity'])
            ->sum(fn (SupplyRequest $approvedRequest): int => max(
                (int) ($approvedRequest->approved_quantity ?? $approvedRequest->quantity)
                    - (int) ($approvedRequest->released_quantity ?? 0),
                0
            ));

        $forwardedReserved = ResqoperationForwardedRequest::where('inventory_id', $inventory->id)
            ->whereIn(DB::raw('LOWER(resqperation_status)'), ['approved', 'in progress', 'in_progress', 'partially released'])
            ->withSum('borrowReleases as released_quantity', 'quantity')
            ->get(['id', 'quantity', 'approved_quantity'])
            ->sum(fn (ResqoperationForwardedRequest $approvedRequest): int => max(
                (int) ($approvedRequest->approved_quantity ?? $approvedRequest->quantity)
                    - (int) ($approvedRequest->released_quantity ?? 0),
                0
            ));

        return max((int) $inventory->quantity - $reserved - $forwardedReserved, 0);
    }

    private function availableToAllocateForForwarded(Inventory $inventory, int $exceptRequestId): int
    {
        $localReserved = SupplyRequest::where('inventory_id', $inventory->id)
            ->whereIn('status', ['Approved', 'Partially Released'])
            ->withSum('borrowReleases as released_quantity', 'quantity')
            ->get(['id', 'quantity', 'approved_quantity'])
            ->sum(fn (SupplyRequest $approvedRequest): int => max(
                (int) ($approvedRequest->approved_quantity ?? $approvedRequest->quantity)
                    - (int) ($approvedRequest->released_quantity ?? 0),
                0
            ));
        $forwardedReserved = ResqoperationForwardedRequest::where('inventory_id', $inventory->id)
            ->whereKeyNot($exceptRequestId)
            ->whereIn(DB::raw('LOWER(resqperation_status)'), ['approved', 'in progress', 'in_progress', 'partially released'])
            ->withSum('borrowReleases as released_quantity', 'quantity')
            ->get(['id', 'quantity', 'approved_quantity'])
            ->sum(fn (ResqoperationForwardedRequest $approvedRequest): int => max(
                (int) ($approvedRequest->approved_quantity ?? $approvedRequest->quantity)
                    - (int) ($approvedRequest->released_quantity ?? 0),
                0
            ));

        return max((int) $inventory->quantity - $localReserved - $forwardedReserved, 0);
    }

    private function externalStockAvailability(?string $itemReference, ?int $requestedQuantity): array
    {
        $reference = trim((string) $itemReference);

        if ($reference === '') {
            return [
                'status' => 'unverified',
                'message' => 'Stock availability could not be verified because no item name or SKU was provided.',
                'requested_quantity' => $requestedQuantity,
                'available_quantity' => null,
                'shortage' => null,
            ];
        }

        $item = Item::with('stockBatches')->where('sku', $reference)->first();
        if (! $item) {
            $matches = Item::with('stockBatches')
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($reference)])
                ->limit(2)
                ->get();

            if ($matches->count() !== 1) {
                return [
                    'status' => $matches->isEmpty() ? 'unverified' : 'ambiguous_item',
                    'message' => $matches->isEmpty()
                        ? 'Stock availability could not be verified because this item does not exactly match an inventory item.'
                        : 'Stock availability could not be verified because this item name matches multiple inventory records. Send its exact SKU.',
                    'requested_quantity' => $requestedQuantity,
                    'available_quantity' => null,
                    'shortage' => null,
                ];
            }

            $item = $matches->first();
        }

        $onHand = max((int) $item->stockBatches->sum('quantity'), 0);
        $inventoryId = Inventory::where('sku', $item->sku)->value('id');
        $reserved = 0;

        if ($inventoryId) {
            $reserved = SupplyRequest::where('inventory_id', $inventoryId)
                ->whereIn('status', ['Approved', 'Partially Released'])
                ->withSum('borrowReleases as released_quantity', 'quantity')
                ->get(['id', 'quantity', 'approved_quantity'])
                ->sum(fn (SupplyRequest $approvedRequest): int => max(
                    (int) ($approvedRequest->approved_quantity ?? $approvedRequest->quantity)
                        - (int) ($approvedRequest->released_quantity ?? 0),
                    0
                ));
        }

        $available = max($onHand - $reserved, 0);
        $shortage = $requestedQuantity === null ? null : max($requestedQuantity - $available, 0);
        $status = $requestedQuantity === null
            ? 'unverified_quantity'
            : ($shortage === 0 ? 'available' : ($available > 0 ? 'partially_available' : 'insufficient'));

        return [
            'status' => $status,
            'message' => $requestedQuantity === null
                ? 'Item matched, but stock fulfillment cannot be checked because the requested quantity was not provided.'
                : ($shortage === 0
                    ? "Requested {$requestedQuantity}; {$available} available."
                    : "Requested {$requestedQuantity}; {$available} available; shortage {$shortage}."),
            'matched_item' => ['name' => $item->name, 'sku' => $item->sku],
            'requested_quantity' => $requestedQuantity,
            'on_hand_quantity' => $onHand,
            'reserved_quantity' => $reserved,
            'available_quantity' => $available,
            'shortage' => $shortage,
        ];
    }
}
