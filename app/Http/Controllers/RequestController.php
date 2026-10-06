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

    public function approveForwarded(ResqoperationForwardedRequest $forwardedRequest)
    {
        if (! in_array(strtolower($forwardedRequest->resqperation_status ?? 'forwarded'), ['forwarded', 'pending'], true)) {
            return back()->with('error', 'This forwarded request has already been reviewed.');
        }

        $forwardedRequest->resqperation_status = 'approved';
        $forwardedRequest->save();

        return back()->with('success', 'ResQperation request approved.');
    }

    public function rejectForwarded(ResqoperationForwardedRequest $forwardedRequest)
    {
        if (! in_array(strtolower($forwardedRequest->resqperation_status ?? 'forwarded'), ['forwarded', 'pending'], true)) {
            return back()->with('error', 'This forwarded request has already been reviewed.');
        }

        $forwardedRequest->resqperation_status = 'rejected';
        $forwardedRequest->save();

        return back()->with('success', 'ResQperation request rejected.');
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

        return response()->json([
            'status'  => 'success',
            'message' => 'Forwarded request successfully received by TrackingAid!',
            'data'    => $forwardedRequest
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
}
