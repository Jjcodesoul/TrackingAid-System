<?php

namespace App\Http\Controllers;

use App\Models\Request as SupplyRequest;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\ResqoperationForwardedRequest;
use App\Services\InventorySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestController extends Controller
{
    public function index(InventorySyncService $inventorySync)
    {
        $inventorySync->syncAllItems();

        $requests = SupplyRequest::with(['inventory.item', 'approvedBy'])
            ->latest()
            ->get();

        $stockAvailability = $this->stockAvailability($requests);

        // 2. Fetch external forwarded requests sent by ResQOperation
        $forwardedRequests = ResqoperationForwardedRequest::latest()->get();
        $forwardedAvailability = $forwardedRequests->mapWithKeys(fn (ResqoperationForwardedRequest $forwardedRequest): array => [
            $forwardedRequest->id => $this->externalStockAvailability($forwardedRequest->item_name ?: $forwardedRequest->resource_type, (int) $forwardedRequest->quantity),
        ]);

        $forwardedStatusGroup = fn ($forwardedRequest) => match (strtolower($forwardedRequest->resqperation_status ?? 'forwarded')) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            'released' => 'released',
            default => 'pending',
        };
        $forwardedStats = $forwardedRequests->countBy($forwardedStatusGroup);

        $stats = [
            'pending'  => $requests->where('status', 'Pending')->count() + $forwardedStats->get('pending', 0),
            'approved' => $requests->where('status', 'Approved')->count() + $forwardedStats->get('approved', 0),
            'released' => $requests->where('status', 'Released')->count() + $forwardedStats->get('released', 0),
            'rejected' => $requests->where('status', 'Rejected')->count() + $forwardedStats->get('rejected', 0),
        ];

        // 4. Pass all variables to view
        return view('requests.index', compact('requests', 'forwardedRequests', 'stats', 'stockAvailability', 'forwardedAvailability'));
    }

    public function approve(Request $httpRequest, SupplyRequest $request, InventorySyncService $inventorySync)
    {
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

        return redirect()
            ->route('borrow-release.create')
            ->with('success', 'Request approved and stock reserved. It is ready for release review.');
    }

    public function reject(SupplyRequest $request)
    {
        DB::transaction(function () use ($request): void {
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
                'approved_at' => null,
            ]);
        });

        return back()->with('success', 'Request rejected.');
    }

    public function approveForwarded(ResqoperationForwardedRequest $forwardedRequest)
    {
        if (! in_array(strtolower($forwardedRequest->resqperation_status ?? 'forwarded'), ['forwarded', 'pending'], true)) {
            return back()->with('error', 'This forwarded request has already been reviewed.');
        }

        $forwardedRequest->resqperation_status = 'approved';
        $forwardedRequest->save();

        return back()->with('success', 'Forwarded request marked approved in TrackingAid. No external status sync is configured.');
    }

    public function rejectForwarded(ResqoperationForwardedRequest $forwardedRequest)
    {
        if (! in_array(strtolower($forwardedRequest->resqperation_status ?? 'forwarded'), ['forwarded', 'pending'], true)) {
            return back()->with('error', 'This forwarded request has already been reviewed.');
        }

        $forwardedRequest->resqperation_status = 'rejected';
        $forwardedRequest->save();

        return back()->with('success', 'Forwarded request marked rejected in TrackingAid. No external status sync is configured.');
    }

    public function storeResqData(Request $request)
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

        $forwardedRequest = ResqoperationForwardedRequest::updateOrCreate(
            ['tracking_reference' => $validated['tracking_reference']],
            $validated
        );

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

        return max((int) $inventory->quantity - $reserved, 0);
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
