<?php

namespace App\Http\Controllers;

use App\Models\Request as SupplyRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function adminIndex(?NotificationService $notificationService = null)
    {
        $notificationService ??= app(NotificationService::class);

        // KPI Stats
        $totalStock       = DB::table('stock_batches')->sum('quantity');
        $activeRequests   = SupplyRequest::where('status', 'Pending')->count();
        $approvedRequests = SupplyRequest::where('status', 'Approved')->count();
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

        return view('admin.dashboard', compact(
            'totalStock',
            'activeRequests',
            'approvedRequests',
            'rejectedRequests',
            'lowStockAlerts',
            'expiringItems',
            'activities',
            'totalRequests'
        ));
    }
}
