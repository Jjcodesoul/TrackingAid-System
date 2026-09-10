<?php

namespace App\Http\Controllers;

use App\Models\BorrowRelease;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * Get deliveries for the mobile app.
     */
    public function index()
    {
        $deliveries = BorrowRelease::with([
            'request',
            'inventory'
        ])
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

    /**
     * Update delivery status.
     */
    public function updateStatus(Request $request, BorrowRelease $delivery)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:Loading,Dispatched,In Transit,Arrived'
            ],
        ]);

        $newStatus = $validated['status'];
        $currentStatus = $delivery->delivery_status;

        $allowedTransitions = [
            'Loading' => ['Dispatched'],
            'Dispatched' => ['In Transit'],
            'In Transit' => ['Arrived'],
            'Arrived' => [],
        ];

        if (
            !isset($allowedTransitions[$currentStatus]) ||
            !in_array($newStatus, $allowedTransitions[$currentStatus])
        ) {
            return response()->json([
                'status' => 'error',
                'message' => "Cannot change delivery status from {$currentStatus} to {$newStatus}.",
            ], 422);
        }

        $delivery->delivery_status = $newStatus;

        if ($newStatus === 'Dispatched') {
            $delivery->dispatched_at = now();
        }

        if ($newStatus === 'In Transit') {
            $delivery->in_transit_at = now();
        }

        if ($newStatus === 'Arrived') {
            $delivery->arrived_at = now();
        }

        $delivery->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery status updated successfully.',
            'data' => $delivery->fresh([
                'request',
                'inventory'
            ]),
        ]);
    }

    /**
     * Get one delivery.
     */
    public function show(BorrowRelease $delivery)
    {
        return response()->json([
            'status' => 'success',
            'data' => $delivery->load([
                'request',
                'inventory'
            ]),
        ]);
    }
}