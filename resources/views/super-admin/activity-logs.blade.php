@extends('layouts.app')

@section('title', 'Super Admin Activity Logs')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">System Activity Logs</h1>
        <p class="page-subtitle">Auditable record of account and operational data changes.</p>
    </div>
    <a href="{{ route('super-admin.dashboard') }}" class="btn-soft">Back to dashboard</a>
</div>

<div role="tablist" aria-label="Activity log views" style="display:flex; gap:8px; margin-bottom:16px;">
    <a role="tab" aria-selected="{{ $tab === 'recent' ? 'true' : 'false' }}" href="{{ route('super-admin.activity-logs', ['tab' => 'recent']) }}" class="btn-{{ $tab === 'recent' ? 'main' : 'soft' }}">All / Recent Activity</a>
    <a role="tab" aria-selected="{{ $tab === 'users' ? 'true' : 'false' }}" href="{{ route('super-admin.activity-logs', ['tab' => 'users']) }}" class="btn-{{ $tab === 'users' ? 'main' : 'soft' }}">By User</a>
</div>

<section class="panel" style="padding:0; overflow:hidden;">
    @if($tab === 'users')
        <form method="GET" action="{{ route('super-admin.activity-logs') }}" class="panel" style="display:flex; gap:12px; align-items:end; border:0; border-bottom:1px solid #e2e8f0; border-radius:0;">
            <input type="hidden" name="tab" value="users">
            <div style="flex:1; min-width:180px;">
                <label for="user-search" class="form-label">Search users</label>
                <input id="user-search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" class="form-input" placeholder="Name, email, or role">
            </div>
            <button type="submit" class="btn-main">Search</button>
            <a href="{{ route('super-admin.activity-logs', ['tab' => 'users']) }}" class="btn-soft">Clear</a>
        </form>

        <div class="overflow-x-auto">
            <table class="table-ui" style="width:100%;">
                <thead><tr><th scope="col">User</th><th scope="col">Role</th><th scope="col">Status</th><th scope="col">Total activity</th><th scope="col">Last activity</th></tr></thead>
                <tbody>
                    @forelse($users as $userItem)
                        <tr>
                            <td>
                                <a href="{{ route('super-admin.activity-logs.users.show', $userItem) }}" style="font-weight:600;">{{ $userItem->name }}</a>
                                <span style="display:block; color:#64748b; font-size:12px;">{{ $userItem->email }}</span>
                            </td>
                            <td>{{ match($userItem->role) { 'admin' => 'Administrator', 'staff' => 'Staff', 'user' => 'User / Borrower', 'delivery_personnel' => 'Delivery Personnel', default => ucfirst(str_replace('_', ' ', $userItem->role)) } }}</td>
                            <td><span class="status-badge {{ $userItem->is_active ? 'status-approved' : 'status-rejected' }}">{{ $userItem->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>{{ number_format($userItem->activity_count) }}</td>
                            <td>{{ $userItem->last_activity ? \Illuminate\Support\Carbon::parse($userItem->last_activity)->format('M d, Y H:i') : 'No activity recorded' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-500">No users match this search.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:16px;">{{ $users->links() }}</div>
    @else
        <form method="GET" action="{{ route('super-admin.activity-logs') }}" class="panel" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap; border:0; border-bottom:1px solid #e2e8f0; border-radius:0;">
            <input type="hidden" name="tab" value="recent">
            <div style="flex:1; min-width:180px;">
                <label for="activity-search" class="form-label">User or record</label>
                <input id="activity-search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" class="form-input" placeholder="Name, email, model, or record ID">
            </div>
            <div style="min-width:150px;">
                <label for="event-type" class="form-label">Action</label>
                <select id="event-type" name="event_type" class="form-select">
                    <option value="">All actions</option>
                    @foreach(['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['event_type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date-from" class="form-label">From date</label>
                <input id="date-from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-input">
            </div>
            <div>
                <label for="date-to" class="form-label">To date</label>
                <input id="date-to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-input">
            </div>
            <button type="submit" class="btn-main">Filter</button>
            <a href="{{ route('super-admin.activity-logs', ['tab' => 'recent']) }}" class="btn-soft">Clear</a>
        </form>

        <div class="overflow-x-auto">
            <table class="table-ui" style="width:100%;">
                <thead><tr><th scope="col">Date and time</th><th scope="col">User</th><th scope="col">Action</th><th scope="col">Affected record</th><th scope="col">Changed values</th></tr></thead>
                <tbody>
                    @forelse($logs as $entry)
                        @php($log = $entry['log'])
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                            <td>
                                @if($log->actor)
                                    <a href="{{ route('super-admin.activity-logs.users.show', $log->actor) }}">{{ $log->actor->name }}</a>
                                @else
                                    System / external
                                @endif
                            </td>
                            <td>{{ $entry['summary'] }}</td>
                            <td>{{ $entry['record'] }}</td>
                            <td>
                                @if($entry['changes'] !== [])
                                    <details>
                                        <summary>View {{ count($entry['changes']) }} {{ $log->event_type === 'updated' ? 'change(s)' : 'details' }}</summary>
                                        <dl style="margin:8px 0 0;">
                                            @foreach($entry['changes'] as $change)
                                                <div style="padding:6px 0; border-top:1px solid #e2e8f0;">
                                                    <dt style="font-weight:600;">{{ $change['label'] }}</dt>
                                                    <dd style="margin:2px 0 0; color:#475569;">
                                                        @if(array_key_exists('old', $change))
                                                            {{ $change['old'] }} <span aria-label="changed to">→</span> <strong>{{ $change['new'] }}</strong>
                                                        @else
                                                            {{ $change['value'] }}
                                                        @endif
                                                    </dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    </details>
                                @else
                                    <span class="text-slate-500">No field changes recorded</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-500">No matching activity was found. Adjust your filters or date range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:16px;">{{ $logs->links() }}</div>
    @endif
</section>
@endsection
