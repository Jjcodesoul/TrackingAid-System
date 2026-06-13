<?php

namespace App\Http\Controllers;

use App\Models\Request as SupplyRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index()
    {
        $requests = SupplyRequest::with('inventory')->latest()->get();
        return view('requests.index', compact('requests'));
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
}
