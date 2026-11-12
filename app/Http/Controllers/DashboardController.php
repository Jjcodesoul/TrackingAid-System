<?php

namespace App\Http\Controllers;

use App\Models\Request as SupplyRequest;
use App\Models\ResqoperationForwardedRequest;
use App\Models\BorrowRelease;
use App\Models\ReturnItem;
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

    public function dashboardData(?NotificationService $notificationService = null): array
    {
        $notificationService ??= app(NotificationService::class);

        // KPI Stats
        $totalStock       = DB::table('stock_batches')->sum('quantity');
        $forwardedStatuses = ResqoperationForwardedRequest::query()
            ->selectRaw("LOWER(COALESCE(resqperation_status, 'forwarded')) as status_key, COUNT(*) as total")
            ->groupBy('status_key')
            ->pluck('total', 'status_key');
        $activeRequests = SupplyRequest::where('status', 'Pending')->count()
            + $forwardedStatuses->only(['forwarded', 'pending', 'received', 'submitted'])->sum();
        $approvedRequests = SupplyRequest::where('status', 'Approved')->count()
            + $forwardedStatuses->get('approved', 0);
        $inProgressRequests = SupplyRequest::where('status', 'Partially Released')->count()
            + $forwardedStatuses->only(['in progress', 'in_progress', 'partially released'])->sum();
        $releasedRequests = SupplyRequest::where('status', 'Released')->count()
            + $forwardedStatuses->only(['released', 'completed'])->sum();
        $releasedItems = BorrowRelease::sum('quantity');
        $returnedItems = ReturnItem::sum('quantity');
        $rejectedRequests = SupplyRequest::where('status', 'Rejected')->count()
            + $forwardedStatuses->get('rejected', 0);
        $lowStockAlerts   = $notificationService->lowStockAlerts()->count();
        $expiringItems    = $notificationService->expiringItemAlerts()->count();

        // Activity Feed — recent requests
        $activities = SupplyRequest::with('inventory')
            ->latest()
            ->take(6)
            ->get()
            ->concat(ResqoperationForwardedRequest::with('inventory')
                ->latest()
                ->take(6)
                ->get()
                ->map(fn (ResqoperationForwardedRequest $request): object => (object) [
                    'request_code' => $request->tracking_reference,
                    'status' => match (strtolower((string) $request->resqperation_status)) {
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'released', 'completed' => 'Released',
                        'in progress', 'in_progress', 'partially released' => 'Partially Released',
                        default => 'Pending',
                    },
                    'quantity' => $request->approved_quantity ?? $request->quantity,
                    'inventory' => $request->inventory,
                    'source' => $request->requested_by ?? $request->source_system,
                    'updated_at' => $request->updated_at,
                ]))
            ->sortByDesc('updated_at')
            ->take(6)
            ->values();

        // Request Status Overview
        $totalRequests = SupplyRequest::count() + ResqoperationForwardedRequest::count();
        $completedDeliveries = BorrowRelease::query()
            ->where('delivery_status', 'Arrived')
            ->whereBetween('arrived_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return compact(
            'totalStock',
            'activeRequests',
            'approvedRequests',
            'inProgressRequests',
            'releasedRequests',
            'releasedItems',
            'returnedItems',
            'rejectedRequests',
            'lowStockAlerts',
            'expiringItems',
            'activities',
            'totalRequests',
            'completedDeliveries'
        );
    }
}
