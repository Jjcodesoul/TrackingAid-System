<?php

namespace App\Http\Controllers;

use App\Models\Request as SupplyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- ADDED THIS so the DB query below works

class RequestController extends Controller
{
    // --- YOUR TEAMMATE'S EXISTING CODE ---

    public function index()
    {
        $requests = SupplyRequest::with('inventory')->latest()->get();
        $stats = [
            'pending' => $requests->where('status', 'Pending')->count(),
            'approved' => $requests->where('status', 'Approved')->count(),
            'released' => $requests->where('status', 'Released')->count(),
            'rejected' => $requests->where('status', 'Rejected')->count(),
        ];

        return view('requests.index', compact('requests', 'stats'));
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
        // 1. Validate the incoming data from Niña's website
        $validated = $request->validate([
            'request_code' => 'required|string|unique:requests,request_code',
            'inventory_id' => 'required|integer',
            'quantity'     => 'required|integer|min:1',
            'priority'     => 'required|in:Low,Medium,High',
            'purpose'      => 'nullable|string',
            'responder_email' => 'nullable|email'
        ]);

        // 2. Save it directly into the TrackingAid database
        DB::table('requests')->insert([
            'request_code'    => $validated['request_code'],
            'inventory_id'    => $validated['inventory_id'],
            'source'          => 'ResQOperation', 
            'quantity'        => $validated['quantity'],
            'priority'        => $validated['priority'],
            'status'          => 'Pending', 
            'purpose'         => $validated['purpose'] ?? null,
            'responder_email' => $validated['responder_email'] ?? null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // 3. Send a "Success" receipt back to Niña's website
        return response()->json([
            'status' => 'success',
            'message' => 'Rescue request successfully received by TrackingAid!',
            'data' => [
                'request_code' => $validated['request_code'],
                'status' => 'Pending'
            ]
        ], 201); 
    }
}