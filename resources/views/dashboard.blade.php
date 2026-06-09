@extends('layouts.app')

@section('content')
<style>
    /* Custom spacing and alignment fixes specifically for the dashboard components */
    .dashboard-container {
        display: flex;
        flex-direction: column;
        gap: 32px; /* Generous vertical gap between rows */
    }

    .metric-card-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 24px; /* Space between cards */
    }

    @media (min-width: 768px) {
        .metric-card-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    .custom-metric-card {
        background: #ffffff;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #edf2f7;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .custom-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .metric-info h3 {
        font-size: 32px;
        font-weight: 700;
        color: #1a202c;
        margin-top: 8px;   /* Clean separation below title */
        margin-bottom: 6px; /* Clean separation above subtitle trend text */
    }

    .metric-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Modern color accents for the icons */
    .icon-blue { background-color: #ebf8ff; color: #3182ce; }
    .icon-green { background-color: #f0fff4; color: #38a169; }
    .icon-amber { background-color: #fffaf0; color: #dd6b20; }
</style>

<div class="dashboard-container">
    <div>
        <h2 class="text-2xl font-bold text-gray-800" style="margin-bottom: 4px;">Dashboard</h2>
        <p class="text-sm text-gray-500">Track and manage your disaster logistics layout metrics.</p>
    </div>

    <div class="metric-card-grid">
        <div class="custom-metric-card">
            <div class="metric-info">
                <span class="text-xs font-semibold tracking-wider text-gray-400 uppercase">Total Inventory</span>
                <h3>0</h3>
                <span class="text-xs text-green-500 font-medium">↗ +12.5% from last month</span>
            </div>
            <div class="metric-icon-wrapper icon-blue">
                <i class="fa-solid fa-boxes-stacked text-2xl"></i>
            </div>
        </div>

        <div class="custom-metric-card">
            <div class="metric-info">
                <span class="text-xs font-semibold tracking-wider text-gray-400 uppercase">Active Requests</span>
                <h3>0</h3>
                <span class="text-xs text-gray-400 font-medium">From ResqOperation</span>
            </div>
            <div class="metric-icon-wrapper icon-green">
                <i class="fa-solid fa-hand-holding-hand text-2xl"></i>
            </div>
        </div>

        <div class="custom-metric-card">
            <div class="metric-info">
                <span class="text-xs font-semibold tracking-wider text-gray-400 uppercase">Low Stock Alerts</span>
                <h3 class="text-red-600">0</h3>
                <span class="text-xs text-amber-500 font-medium">⚠️ Requires attention</span>
            </div>
            <div class="metric-icon-wrapper icon-amber">
                <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 24px;" class="lg:grid-cols-3">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
            <div class="flex items-center gap-2 mb-4" style="margin-bottom: 16px;">
                <i class="fa-solid fa-chart-line text-gray-400" style="margin-right: 6px;"></i>
                <h4 class="font-semibold text-gray-700">Inventory Trend</h4>
            </div>
            <div class="h-64 border border-dashed border-gray-200 rounded-lg flex items-center justify-center bg-gray-50" style="min-height: 250px;">
                <span class="text-sm text-gray-400">Chart data loading from model...</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-4" style="margin-bottom: 16px;">
                <i class="fa-solid fa-chart-pie text-gray-400" style="margin-right: 6px;"></i>
                <h4 class="font-semibold text-gray-700">Request Status Overview</h4>
            </div>
            <div class="space-y-4" style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <div class="flex justify-between text-sm mb-1" style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span class="text-gray-600">Pending</span>
                        <span class="font-semibold text-gray-700">15</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden" style="background-color: #edf2f7; height: 8px; border-radius: 9999px;">
                        <div class="bg-amber-500 h-full" style="width: 26%; background-color: #f6ad55; height: 100%; border-radius: 9999px;"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1" style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span class="text-gray-600">Approved</span>
                        <span class="font-semibold text-gray-700">42</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden" style="background-color: #edf2f7; height: 8px; border-radius: 9999px;">
                        <div class="bg-green-500 h-full" style="width: 74%; background-color: #48bb78; height: 100%; border-radius: 9999px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection