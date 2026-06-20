<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Request as SupplyRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function adminIndex()
    {
        // KPI Stats
        $totalStock       = DB::table('stock_batches')->sum('quantity');
        $activeRequests   = SupplyRequest::where('status', 'Pending')->count();
        $approvedRequests = SupplyRequest::where('status', 'Approved')->count();
        $rejectedRequests = SupplyRequest::where('status', 'Rejected')->count();
        $lowStockAlerts   = Item::with('stockBatches')
                                ->get()
                                ->filter(fn($i) => $i->total_stock > 0 && $i->total_stock < 10)
                                ->count();
        $expiringItems    = 0;

        if (Schema::hasColumn('items', 'expiration_date')) {
            $expiringItems = Item::whereNotNull('expiration_date')
                ->where('expiration_date', '<=', Carbon::now()->addDays(7))
                ->count();
        } elseif (Schema::hasColumn('items', 'expiration')) {
            $expiringItems = Item::whereNotNull('expiration')
                ->where('expiration', '<=', Carbon::now()->addDays(7))
                ->count();
        }

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
