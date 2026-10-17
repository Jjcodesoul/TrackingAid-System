@extends('layouts.app')

@section('content')

{{-- HEADER --}}
<div class="page-header">
    <h2 class="page-title">Dashboard</h2>
</div>

{{-- TOP KPI ROW --}}
<div class="dashboard-kpi-grid">

    <a href="/inventory" class="dashboard-stat-card">
        <div>
            <p class="dashboard-stat-label">Total Inventory Stock</p>
            <h3 class="dashboard-stat-value">{{ number_format($totalStock) }}</h3>
            <p class="dashboard-stat-hint">Across all warehouses</p>
        </div>
        <span class="dashboard-stat-icon tone-info" aria-hidden="true">
            <i class="fa-solid fa-boxes-stacked"></i>
        </span>
    </a>

    <a href="/requests" class="dashboard-stat-card">
        <div>
            <p class="dashboard-stat-label">Active Requests</p>
            <h3 class="dashboard-stat-value">{{ $activeRequests }}</h3>
            <p class="dashboard-stat-hint">Awaiting review</p>
        </div>
        <span class="dashboard-stat-icon" aria-hidden="true">
            <i class="fa-solid fa-list-check"></i>
        </span>
    </a>

    <a href="/requests" class="dashboard-stat-card">
        <div>
            <p class="dashboard-stat-label">Approved Requests</p>
            <h3 class="dashboard-stat-value">{{ $approvedRequests }}</h3>
            <p class="dashboard-stat-hint">All-time approved</p>
        </div>
        <span class="dashboard-stat-icon" aria-hidden="true">
            <i class="fa-regular fa-circle-check"></i>
        </span>
    </a>

</div>

{{-- BOTTOM KPI ROW --}}
<div class="dashboard-kpi-grid dashboard-kpi-grid--bottom">

    <a href="/inventory" class="dashboard-stat-card">
        <div>
            <p class="dashboard-stat-label">Low Stock Alerts</p>
            <h3 class="dashboard-stat-value">{{ $lowStockAlerts }}</h3>
            <p class="dashboard-stat-hint">Below minimum threshold</p>
        </div>
        <span class="dashboard-stat-icon tone-warning" aria-hidden="true">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </span>
    </a>

    <a href="/inventory" class="dashboard-stat-card">
        <div>
            <p class="dashboard-stat-label">Expiring Items</p>
            <h3 class="dashboard-stat-value">{{ $expiringItems }}</h3>
            <p class="dashboard-stat-hint">Expired or expiring within 30 days</p>
        </div>
        <span class="dashboard-stat-icon tone-warning" aria-hidden="true">
            <i class="fa-regular fa-clock"></i>
        </span>
    </a>

    <div class="dashboard-stat-card">
        <div>
            <p class="dashboard-stat-label">Completed Deliveries</p>
            <h3 class="dashboard-stat-value">{{ number_format($completedDeliveries) }}</h3>
            <p class="dashboard-stat-hint">Arrived this month</p>
        </div>
        <span class="dashboard-stat-icon tone-info" aria-hidden="true">
            <i class="fa-solid fa-truck"></i>
        </span>
    </div>

</div>

{{-- REQUEST STATUS OVERVIEW + ACTIVITY FEED --}}
<div class="dashboard-lower-grid">

    {{-- REQUEST STATUS OVERVIEW (now first) --}}
    <section class="panel dashboard-panel">
        <h4 class="dashboard-section-title">Request Status Overview</h4>
        @php
            $total = max($totalRequests, 1);
            $pendingPct  = round(($activeRequests / $total) * 100);
            $approvedPct = round(($approvedRequests / $total) * 100);
            $releasedPct = round(($releasedRequests / $total) * 100);
            $rejectedPct = round(($rejectedRequests / $total) * 100);
        @endphp
        <div class="dashboard-progress-list">
            <div class="dashboard-progress-item">
                <div class="dashboard-progress-label">
                    <span>Pending</span>
                    <strong>{{ $activeRequests }}</strong>
                </div>
                <div class="dashboard-progress-track">
                    <span class="dashboard-progress-fill is-pending" style="width:{{ $pendingPct }}%"></span>
                </div>
            </div>
            <div class="dashboard-progress-item">
                <div class="dashboard-progress-label">
                    <span>Approved</span>
                    <strong>{{ $approvedRequests }}</strong>
                </div>
                <div class="dashboard-progress-track">
                    <span class="dashboard-progress-fill" style="width:{{ $approvedPct }}%"></span>
                </div>
            </div>
            <div class="dashboard-progress-item">
                <div class="dashboard-progress-label">
                    <span>Released</span>
                    <strong>{{ $releasedRequests }}</strong>
                </div>
                <div class="dashboard-progress-track">
                    <span class="dashboard-progress-fill" style="width:{{ $releasedPct }}%"></span>
                </div>
            </div>
            <div class="dashboard-progress-item">
                <div class="dashboard-progress-label">
                    <span>Rejected</span>
                    <strong>{{ $rejectedRequests }}</strong>
                </div>
                <div class="dashboard-progress-track">
                    <span class="dashboard-progress-fill is-rejected" style="width:{{ $rejectedPct }}%"></span>
                </div>
            </div>
        </div>
        <a href="/requests" class="dashboard-view-link">View all requests</a>
    </section>

    {{-- ACTIVITY FEED (now second) --}}
    <section class="panel dashboard-panel">
        <h4 class="dashboard-section-title">Activity Feed</h4>
        <div class="dashboard-activity-list">
            @forelse($activities as $activity)
                @php
                    $dotClass = match($activity->status) {
                        'Approved' => 'is-approved',
                        'Rejected' => 'is-rejected',
                        default    => '',
                    };
                @endphp
                <div class="dashboard-activity-item">
                    <span class="dashboard-activity-dot {{ $dotClass }}" aria-hidden="true"></span>
                    <div>
                        <p class="dashboard-activity-copy">
                            {{ $activity->request_code }} — {{ $activity->status }} —
                            {{ $activity->quantity }} {{ $activity->inventory?->unit_type ?? 'units' }}
                            of {{ $activity->inventory?->name ?? 'item' }}
                            @if($activity->source) — {{ $activity->source }} @endif
                        </p>
                        <p class="dashboard-activity-time">
                            {{ \Carbon\Carbon::parse($activity->updated_at)->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No recent activity.</p>
            @endforelse
        </div>
    </section>

</div>

@endsection
