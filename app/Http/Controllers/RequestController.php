<?php

namespace App\Http\Controllers;

use App\Models\Request as SupplyRequest;
use App\Models\ResqoperationForwardedRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    public function index()
    {
        // 1. Fetch local supply requests with inventory details
        $requests = SupplyRequest::with('inventory')->latest()->get();

        // 2. Fetch external forwarded requests sent by ResQOperation
        $forwardedRequests = ResqoperationForwardedRequest::latest()->get();

        // 3. Calculate combined statistics safely
        $stats = [
            'pending'  => $requests->where('status', 'Pending')->count() + $forwardedRequests->count(),
            'approved' => $requests->where('status', 'Approved')->count(),
            'released' => $requests->where('status', 'Released')->count(),
            'rejected' => $requests->where('status', 'Rejected')->count(),
        ];

        // 4. Pass all variables to view
        return view('requests.index', compact('requests', 'forwardedRequests', 'stats'));
    }

    public function approve(SupplyRequest $request)
    {
        $request->status = 'Approved';
        $request->notification_status = 'Responder notified: request approved';
        $request->approved_at = now();
        $request->rejected_at = null;
        $request->save();

        return back()->with('success', 'Request approved.');
    }

    public function reject(SupplyRequest $request)
    {
        $request->status = 'Rejected';
        $request->notification_status = 'Responder notified: request rejected';
        $request->rejected_at = now();
        $request->approved_at = null;
        $request->save();

        return back()->with('success', 'Request rejected.');
    }

    public function storeResqData(Request $request)
    {
        // Validate exact payload structure matching resqoperation_forwarded_requests schema
        $validated = $request->validate([
            'tracking_reference'       => 'required|string',
            'resqoperation_request_id' => 'nullable|string',
            'source_reference'         => 'nullable|string',
            'request_source'           => 'nullable|string',
            'source_system'            => 'nullable|string',
            'request_category'         => 'nullable|string',
            'resource_type'            => 'nullable|string',
            'item_name'                => 'required|string',
            'quantity'                 => 'required|integer|min:1',
            'unit'                     => 'nullable|string',
            'urgency'                  => 'nullable|string',
            'area_label'               => 'nullable|string',
            'area_note'                => 'nullable|string',
        ]);

        // Create the forwarded record
        $forwardedRequest = ResqoperationForwardedRequest::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Forwarded request successfully received by TrackingAid!',
            'data'    => $forwardedRequest
        ], 201);
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
}
