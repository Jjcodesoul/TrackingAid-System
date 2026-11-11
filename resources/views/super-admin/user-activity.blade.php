@extends('layouts.app')

@section('title', 'User Activity')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $userItem->name }}</h1>
        <p class="page-subtitle">
            {{ $userItem->email }} · {{ match($userItem->role) { 'super_admin' => 'Super Admin', 'admin' => 'Administrator', 'staff' => 'Staff', 'user' => 'User / Borrower', 'delivery_personnel' => 'Delivery Personnel', default => ucfirst(str_replace('_', ' ', $userItem->role)) } }}
            · {{ $userItem->is_active ? 'Active account' : 'Inactive account' }}
            · Created {{ $userItem->created_at?->format('M d, Y H:i') ?? 'date not recorded' }}
        </p>
    </div>
    <a href="{{ route('super-admin.activity-logs', ['tab' => 'users']) }}" class="btn-soft">Back to users</a>
</div>

<div class="dashboard-kpi-grid">
    <div class="dashboard-stat-card"><div><p class="dashboard-stat-label">Total Activity</p><h2 class="dashboard-stat-value">{{ number_format($totalActivity) }}</h2></div><span class="dashboard-stat-icon tone-info" aria-hidden="true"><i class="fa-solid fa-list-check"></i></span></div>
    <div class="dashboard-stat-card"><div><p class="dashboard-stat-label">Activity Today</p><h2 class="dashboard-stat-value">{{ number_format($todayActivity) }}</h2></div><span class="dashboard-stat-icon" aria-hidden="true"><i class="fa-regular fa-calendar-check"></i></span></div>
    <div class="dashboard-stat-card"><div><p class="dashboard-stat-label">Last Active</p><h2 class="dashboard-stat-value" style="font-size:18px;">{{ $lastActivity ? \Illuminate\Support\Carbon::parse($lastActivity)->format('M d, Y H:i') : 'No activity' }}</h2></div><span class="dashboard-stat-icon" aria-hidden="true"><i class="fa-regular fa-clock"></i></span></div>
</div>

<section class="panel dashboard-panel" style="margin-top:20px; padding:0; overflow:hidden;">
    <div class="p-5">
        <h2 class="dashboard-section-title">System Activity</h2>
        <p class="page-subtitle">Only recorded activity is shown; actions from before tracking was enabled may be unavailable.</p>
        <form method="GET" action="{{ route('super-admin.activity-logs.users.show', $userItem) }}" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap; margin-top:14px;">
            <div style="min-width:160px;">
                <label for="user-event-type" class="form-label">Action</label>
                <select id="user-event-type" name="event_type" class="form-select">
                    <option value="">All actions</option>
                    @foreach(['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted', 'csv_imported' => 'CSV imported', 'security_action' => 'Security action', 'password_reset_link_sent' => 'Password reset link sent', 'logged_in' => 'Logged in', 'logged_out' => 'Logged out'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['event_type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:160px;">
                <label for="user-role-filter" class="form-label">Actor role</label>
                <select id="user-role-filter" name="role" class="form-select">
                    <option value="">All roles</option>
                    @foreach($roleOptions as $role)
                        <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ match($role) { 'super_admin' => 'Super Admin', 'admin' => 'Administrator', 'staff' => 'Staff', 'user' => 'User / Borrower', 'delivery_personnel' => 'Delivery Personnel', default => ucfirst(str_replace('_', ' ', $role)) } }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:160px;">
                <label for="user-module-filter" class="form-label">Module</label>
                <select id="user-module-filter" name="module" class="form-select">
                    <option value="">All modules</option>
                    @foreach(['accounts' => 'User Accounts', 'authentication' => 'Authentication', 'inventory' => 'Inventory', 'stock' => 'Stock-In', 'requests' => 'Supply Requests', 'borrow_release' => 'Borrow / Release', 'returns' => 'Returns', 'storage' => 'Storage Locations'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['module'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div><label for="user-date-from" class="form-label">From date</label><input id="user-date-from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-input"></div>
            <div><label for="user-date-to" class="form-label">To date</label><input id="user-date-to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-input"></div>
            <button type="submit" class="btn-main">Filter</button>
            <a href="{{ route('super-admin.activity-logs.users.show', $userItem) }}" class="btn-soft">Clear</a>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table-ui" style="width:100%;">
            <thead><tr><th scope="col">Date and time</th><th scope="col">Actor</th><th scope="col">Module</th><th scope="col">Action</th><th scope="col">Affected record</th><th scope="col">Changes</th></tr></thead>
            <tbody>
                @forelse($logs as $entry)
                    @php($log = $entry['log'])
                    <tr>
                        <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        <td>{{ $log->actor?->name ?? 'System / external' }}</td>
                        <td>{{ $entry['module'] }}</td>
                        <td>{{ $entry['summary'] }}</td>
                        <td>{{ $entry['record'] }}</td>
                        <td>
                            @if($entry['changes'] !== [])
                                <details><summary>View {{ count($entry['changes']) }} change(s)</summary>
                                    <ul style="margin:8px 0 0; padding-left:20px;">
                                        @foreach($entry['changes'] as $change)
                                            <li><strong>{{ $change['label'] }}:</strong>
                                                @if(array_key_exists('old', $change))
                                                    {{ $change['old'] }} → {{ $change['new'] }}
                                                @else
                                                    {{ $change['value'] }}
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @else
                                <span class="text-slate-500">No field changes</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center text-slate-500">No activity matches the selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:16px;">{{ $logs->links() }}</div>
</section>

<section class="panel dashboard-panel" style="margin-top:20px; padding:0; overflow:hidden;">
    <div class="p-5">
        <h2 class="dashboard-section-title">Login History</h2>
        <p class="page-subtitle">Successful and failed sign-in attempts recorded for this account.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="table-ui" style="width:100%;">
            <thead><tr><th scope="col">Date and time</th><th scope="col">Result</th><th scope="col">IP address</th><th scope="col">Device / browser</th></tr></thead>
            <tbody>
                @forelse($loginEvents as $event)
                    <tr>
                        <td>{{ $event->created_at->format('M d, Y H:i:s') }}</td>
                        <td><span class="status-badge {{ $event->succeeded ? 'status-approved' : 'status-rejected' }}">{{ $event->succeeded ? 'Successful' : 'Failed' }}</span></td>
                        <td>{{ $event->ip_address ?: 'Not recorded' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($event->user_agent ?: 'Not recorded', 100) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center text-slate-500">No login attempts are recorded for this account yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:16px;">{{ $loginEvents->links() }}</div>
</section>
@endsection
