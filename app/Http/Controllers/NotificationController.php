<?php

namespace App\Http\Controllers;

use App\Models\Request as SupplyRequest;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // Mark all current notifications as "read" by recording the timestamp
        session(['notifications_last_viewed_at' => now()]);

        $notifications = SupplyRequest::whereNotNull('notification_status')
            ->latest('updated_at')
            ->get()
            ->map(function ($request) {
                $request->read = false;
                return $request;
            });

        // Use shared unread count from AppServiceProvider via the view
        return view('notifications', [
            'notifications' => $notifications,
            'unreadCount' => $notifications->count(),
        ]);
    }

    /**
     * JSON endpoint for polling – returns the current unread count
     * and the latest notification details so the front-end can
     * show browser alerts and update the badge in real time.
     */
    public function unreadJson(Request $request)
    {
        $lastViewedAt = session('notifications_last_viewed_at');

        $unreadCount = SupplyRequest::whereNotNull('notification_status')
            ->when($lastViewedAt, fn ($q) => $q->where('updated_at', '>', $lastViewedAt))
            ->count();

        // Grab the single most-recent notification for the alert message
        $latest = SupplyRequest::whereNotNull('notification_status')
            ->with('inventory')
            ->latest('updated_at')
            ->first();

        return response()->json([
            'unreadCount' => $unreadCount,
            'latest' => $latest ? [
                'request_code' => $latest->request_code,
                'notification_status' => $latest->notification_status,
                'item_name' => $latest->inventory?->name,
                'updated_at' => $latest->updated_at?->toIso8601String(),
            ] : null,
        ]);
    }
}
