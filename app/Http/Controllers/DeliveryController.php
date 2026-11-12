<?php

namespace App\Http\Controllers;

use App\Models\BorrowRelease;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function dashboard()
{
    $deliveries = BorrowRelease::with([
        'request',
        'forwardedRequest',
        'inventory',
        'deliveryPersonnel'
    ])
    ->where('delivery_personnel_id', auth()->id())
    ->latest()
    ->get();

    $counts = [
        'Loading' => $deliveries->where('delivery_status', 'Loading')->count(),
        'Dispatched' => $deliveries->where('delivery_status', 'Dispatched')->count(),
        'In Transit' => $deliveries->where('delivery_status', 'In Transit')->count(),
        'Arrived' => $deliveries->where('delivery_status', 'Arrived')->count(),
    ];

    $currentShipment = $deliveries
        ->whereIn('delivery_status', [
            'Loading',
            'Dispatched',
            'In Transit',
        ])
        ->first();

    return response()->json([
        'status' => 'success',
        'counts' => $counts,
        'current_shipment' => $currentShipment,
    ]);
}
    public function index()
{
    $deliveries = BorrowRelease::with([
        'request',
        'forwardedRequest',
        'inventory',
        'deliveryPersonnel'
    ])
    ->where('delivery_personnel_id', auth()->id())
    ->whereIn('delivery_status', [
        'Loading',
        'Dispatched',
        'In Transit',
    ])
    ->latest()
    ->get();

    return response()->json([
        'status' => 'success',
        'data' => $deliveries,
    ]);
}

public function history()
{
    $deliveries = BorrowRelease::with([
        'request',
        'forwardedRequest',
        'inventory',
        'deliveryPersonnel'
    ])
    ->where('delivery_personnel_id', auth()->id())
    ->where('delivery_status', 'Arrived')
    ->latest('arrived_at')
    ->get();

    return response()->json([
        'status' => 'success',
        'data' => $deliveries,
    ]);
}

    /**
     * Update delivery status.
     */
    public function updateStatus(Request $request, BorrowRelease $delivery, NotificationService $notifications)
    {

        if ((int) $delivery->delivery_personnel_id !== (int) auth()->id()) {
    return response()->json([
        'status' => 'error',
        'message' => 'You are not assigned to this delivery.',
    ], 403);
        }

        $validated = $request->validate([
            'status' => [
                'required',
                'in:Loading,Dispatched,In Transit,Arrived'
            ],
        ]);

        $newStatus = $validated['status'];
        $updated = DB::transaction(function () use ($delivery, $newStatus): bool {
            $lockedDelivery = BorrowRelease::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            $allowedTransitions = [
                'Loading' => ['Dispatched'],
                'Dispatched' => ['In Transit'],
                'In Transit' => ['Arrived'],
                'Arrived' => [],
            ];
            if (
                (int) $lockedDelivery->delivery_personnel_id !== (int) auth()->id()
                || ! in_array($newStatus, $allowedTransitions[$lockedDelivery->delivery_status] ?? [], true)
            ) {
                return false;
            }

            $lockedDelivery->delivery_status = $newStatus;
            if ($newStatus === 'Dispatched') {
                $lockedDelivery->dispatched_at = now();
            }
            if ($newStatus === 'In Transit') {
                $lockedDelivery->in_transit_at = now();
            }
            if ($newStatus === 'Arrived') {
                $lockedDelivery->arrived_at = now();
            }
            $lockedDelivery->save();

            return true;
        });

        if (! $updated) {
            return response()->json([
                'status' => 'error',
                'message' => 'The delivery status changed or the requested transition is not allowed. Refresh and try again.',
            ], 422);
        }

        $delivery->refresh();
        $requestCode = $delivery->request?->request_code
            ?? $delivery->forwardedRequest?->tracking_reference
            ?? 'Release #'.$delivery->id;
        $notifications->notifyRoles(
            ['admin', 'staff'],
            'Delivery status updated',
            "Delivery for request {$requestCode} is now {$newStatus}.",
            route('delivery-monitoring.index', [], false),
            'delivery_status_updated',
            $requestCode,
            $delivery->inventory?->name,
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery status updated successfully.',
            'data' => $delivery->fresh([
                'request',
                'forwardedRequest',
                'inventory'
            ]),
        ]);
    }

    /**
     * Get one delivery.
     */
    public function show(BorrowRelease $delivery)
{
    if ((int) $delivery->delivery_personnel_id !== (int) auth()->id()) {
        return response()->json([
            'status' => 'error',
            'message' => 'You are not assigned to this delivery.',
        ], 403);
    }

    return response()->json([
        'status' => 'success',
        'data' => $delivery->load([
            'request',
            'forwardedRequest',
            'inventory',
            'deliveryPersonnel'
        ]),
    ]);
}
}