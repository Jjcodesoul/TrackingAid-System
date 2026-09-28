<?php

namespace App\Http\Controllers;

use App\Models\Request as SupplyRequest;
use App\Models\BorrowRelease;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', $this->dashboardData());
    }

    public function adminIndex(?NotificationService $notificationService = null)
    {
        return view('admin.dashboard', $this->dashboardData($notificationService));
    }

    private function dashboardData(?NotificationService $notificationService = null): array
    {
        $notificationService ??= app(NotificationService::class);

        // KPI Stats
        $totalStock       = DB::table('stock_batches')->sum('quantity');
        $activeRequests   = SupplyRequest::where('status', 'Pending')->count();
        $approvedRequests = SupplyRequest::where('status', 'Approved')->count();
        $releasedRequests = SupplyRequest::where('status', 'Released')->count();
        $rejectedRequests = SupplyRequest::where('status', 'Rejected')->count();
        $lowStockAlerts   = $notificationService->lowStockAlerts()->count();
        $expiringItems    = $notificationService->expiringItemAlerts()->count();

        // Activity Feed — recent requests
        $activities = SupplyRequest::with('inventory')
                        ->latest()
                        ->take(6)
                        ->get();

        // Request Status Overview
        $totalRequests = SupplyRequest::count();
        $completedDeliveries = BorrowRelease::query()
            ->where('delivery_status', 'Arrived')
            ->whereBetween('arrived_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return compact(
            'totalStock',
            'activeRequests',
            'approvedRequests',
            'releasedRequests',
            'rejectedRequests',
            'lowStockAlerts',
            'expiringItems',
            'activities',
            'totalRequests',
            'completedDeliveries'
        );
    }
}
