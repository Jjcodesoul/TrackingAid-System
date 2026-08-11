<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index(NotificationService $notificationsService)
    {
        $lastViewedAt = $this->lastViewedAt();
        $notifications = $notificationsService->all($lastViewedAt);

        // Mark current notifications as read after calculating row badges.
        session(['notifications_last_viewed_at' => now()]);

        return view('notifications', [
            'notifications' => $notifications,
            'unreadCount' => 0,
        ]);
    }

    /**
     * JSON endpoint for polling – returns the current unread count
     * and the latest notification details so the front-end can
     * show browser alerts and update the badge in real time.
     */
    public function unreadJson(Request $request, NotificationService $notificationsService)
    {
        $unread = $notificationsService->unread($this->lastViewedAt());
        $latest = $unread->first();

        return response()->json([
            'unreadCount' => $unread->count(),
            'latest' => $latest ? $this->jsonAlert($latest) : null,
        ]);
    }

    private function lastViewedAt(): ?Carbon
    {
        $lastViewedAt = session('notifications_last_viewed_at');

        return $lastViewedAt ? Carbon::parse($lastViewedAt) : null;
    }

    private function jsonAlert(array $alert): array
    {
        return [
            'id' => $alert['id'],
            'type' => $alert['type'],
            'title' => $alert['title'],
            'message' => $alert['message'],
            'request_code' => $alert['request_code'],
            'item_name' => $alert['item_name'],
            'url' => $alert['url'],
            'updated_at' => $alert['updated_at']->toIso8601String(),
        ];
    }
}
