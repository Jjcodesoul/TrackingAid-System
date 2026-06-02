<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
   public function index()
{
    $stats = [
        'total_stock'          => 0,
        'active_requests'      => 0,
        'low_stock'            => 0,
        'expiring_items'       => 0,
        'completed_deliveries' => 0,
    ];

    // Change 'admin.dashboard' to 'dashboard' right here:
    return view('dashboard', compact('stats'));
}
}