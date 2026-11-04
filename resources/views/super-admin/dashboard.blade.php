@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Super Admin Dashboard</h1>
        <p class="page-subtitle">Operational overview, account oversight, and security activity.</p>
    </div>
</div>

<div class="dashboard-kpi-grid">
    <a href="{{ route('users.index') }}" class="dashboard-stat-card">
        <div>
            <p class="dashboard-stat-label">All User Accounts</p>
            <h2 class="dashboard-stat-value">{{ number_format($userCount) }}</h2>
            <p class="dashboard-stat-hint">{{ number_format($adminCount) }} administrators</p>
        </div>
        <span class="dashboard-stat-icon tone-info" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
    </a>
    <a href="{{ route('users.index', ['is_active' => 1]) }}" class="dashboard-stat-card">
        <div>
            <p class="dashboard-stat-label">Active Accounts</p>
            <h2 class="dashboard-stat-value">{{ number_format($activeUserCount) }}</h2>
            <p class="dashboard-stat-hint">Can sign in</p>
        </div>
        <span class="dashboard-stat-icon" aria-hidden="true"><i class="fa-solid fa-user-check"></i></span>
    </a>
    <a href="{{ route('users.index', ['is_active' => 0]) }}" class="dashboard-stat-card">
        <div>
            <p class="dashboard-stat-label">Deactivated Accounts</p>
            <h2 class="dashboard-stat-value">{{ number_format($inactiveUserCount) }}</h2>
            <p class="dashboard-stat-hint">Sign-in blocked</p>
        </div>
        <span class="dashboard-stat-icon tone-warning" aria-hidden="true"><i class="fa-solid fa-user-slash"></i></span>
    </a>
    <a href="{{ route('inventory.index') }}" class="dashboard-stat-card">
        <div><p class="dashboard-stat-label">Total Inventory Stock</p><h2 class="dashboard-stat-value">{{ number_format($totalStock) }}</h2></div>
        <span class="dashboard-stat-icon tone-info" aria-hidden="true"><i class="fa-solid fa-boxes-stacked"></i></span>
    </a>
    <a href="{{ route('requests.index') }}" class="dashboard-stat-card">
        <div><p class="dashboard-stat-label">Pending Requests</p><h2 class="dashboard-stat-value">{{ number_format($activeRequests) }}</h2></div>
        <span class="dashboard-stat-icon" aria-hidden="true"><i class="fa-solid fa-list-check"></i></span>
    </a>
    <a href="{{ route('requests.index') }}" class="dashboard-stat-card">
        <div><p class="dashboard-stat-label">Approved Requests</p><h2 class="dashboard-stat-value">{{ number_format($approvedRequests) }}</h2></div>
        <span class="dashboard-stat-icon" aria-hidden="true"><i class="fa-regular fa-circle-check"></i></span>
    </a>
    <a href="{{ route('borrow-release.create') }}" class="dashboard-stat-card">
        <div><p class="dashboard-stat-label">Items Released</p><h2 class="dashboard-stat-value">{{ number_format($releasedItems) }}</h2></div>
        <span class="dashboard-stat-icon tone-info" aria-hidden="true"><i class="fa-solid fa-box-open"></i></span>
    </a>
    <a href="{{ route('inventory.index') }}" class="dashboard-stat-card">
        <div><p class="dashboard-stat-label">Low Stock Alerts</p><h2 class="dashboard-stat-value">{{ number_format($lowStockAlerts) }}</h2></div>
        <span class="dashboard-stat-icon tone-warning" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></span>
    </a>
    <a href="{{ route('inventory.index') }}" class="dashboard-stat-card">
        <div><p class="dashboard-stat-label">Expiring Items</p><h2 class="dashboard-stat-value">{{ number_format($expiringItems) }}</h2></div>
        <span class="dashboard-stat-icon tone-warning" aria-hidden="true"><i class="fa-regular fa-clock"></i></span>
    </a>
    <a href="{{ route('delivery-monitoring.index') }}" class="dashboard-stat-card">
        <div><p class="dashboard-stat-label">Completed Deliveries</p><h2 class="dashboard-stat-value">{{ number_format($completedDeliveries) }}</h2><p class="dashboard-stat-hint">Arrived this month</p></div>
        <span class="dashboard-stat-icon tone-info" aria-hidden="true"><i class="fa-solid fa-truck"></i></span>
    </a>
    <a href="{{ route('returns.create') }}" class="dashboard-stat-card">
        <div><p class="dashboard-stat-label">Items Returned</p><h2 class="dashboard-stat-value">{{ number_format($returnedItems) }}</h2></div>
        <span class="dashboard-stat-icon" aria-hidden="true"><i class="fa-solid fa-rotate-left"></i></span>
    </a>
</div>

<section class="panel dashboard-panel" style="margin-top:20px;">
    <div class="page-header" style="margin-bottom:16px;">
        <div>
            <h2 class="dashboard-section-title">Users by Role</h2>
            <p class="page-subtitle">Active and deactivated account counts.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn-soft">View all users</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table-ui" style="width:100%;">
            <thead><tr><th scope="col">Role</th><th scope="col">Total</th><th scope="col">Active</th><th scope="col">Deactivated</th></tr></thead>
            <tbody>
                @forelse($roleCounts as $roleCount)
                    <tr>
                        <td>{{ match($roleCount->role) { 'admin' => 'Administrator', 'staff' => 'Staff', 'user' => 'User / Borrower', 'delivery_personnel' => 'Delivery Personnel', default => ucfirst(str_replace('_', ' ', $roleCount->role)) } }}</td>
                        <td><a href="{{ route('users.index', ['role' => $roleCount->role]) }}">{{ number_format($roleCount->total) }}</a></td>
                        <td>{{ number_format($roleCount->active_count) }}</td>
                        <td>{{ number_format($roleCount->total - $roleCount->active_count) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-6 text-center text-slate-500">No user accounts to summarize.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="panel dashboard-panel" style="margin-top:20px;">
    <div class="page-header" style="margin-bottom:16px;">
        <div>
            <h2 class="dashboard-section-title">Recently Active Users</h2>
            <p class="page-subtitle">Accounts ordered by most recent successful sign-in.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn-soft">View all users</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table-ui" style="width:100%;">
            <thead><tr><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Status</th><th scope="col">Last active</th></tr></thead>
            <tbody>
                @forelse($recentUsers as $userItem)
                    <tr>
                        <td><a href="{{ route('super-admin.activity-logs.users.show', $userItem) }}">{{ $userItem->name }}</a></td>
                        <td>{{ $userItem->email }}</td>
                        <td>{{ match($userItem->role) { 'admin' => 'Administrator', 'staff' => 'Staff', 'user' => 'User / Borrower', 'delivery_personnel' => 'Delivery Personnel', default => ucfirst(str_replace('_', ' ', $userItem->role)) } }}</td>
                        <td><span class="status-badge {{ $userItem->is_active ? 'status-approved' : 'status-rejected' }}">{{ $userItem->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>{{ $userItem->last_login_at?->format('M d, Y H:i') ?? 'Never signed in' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-slate-500">No user accounts are available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="panel dashboard-panel" style="margin-top:20px;">
    <div class="page-header" style="margin-bottom:16px;">
        <div>
            <h2 class="dashboard-section-title">Recent System Activity</h2>
            <p class="page-subtitle">Changes to accounts, inventory, requests, releases, and returns.</p>
        </div>
        <a href="{{ route('super-admin.activity-logs') }}" class="btn-soft">View all activity</a>
    </div>

    <div class="overflow-x-auto">
        <table class="table-ui" style="width:100%;">
            <thead>
                <tr>
                    <th scope="col">Date and time</th>
                    <th scope="col">User</th>
                    <th scope="col">Action</th>
                    <th scope="col">Affected record</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogs as $entry)
                    @php($log = $entry['log'])
                    <tr>
                        <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        <td>{{ $log->actor?->name ?? 'System / external' }}</td>
                        <td>{{ $entry['summary'] }}</td>
                        <td>{{ $entry['record'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-6 text-center text-slate-500">No recorded system activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@endsection
