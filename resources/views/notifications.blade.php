@extends('layouts.app')

@section('content')
<style>
    .notif-container { display: flex; flex-direction: column; gap: 20px; width: 100%; }
    .notif-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--line); padding-bottom: 14px; }
    .notif-title h2 { margin: 0; color: var(--ink); font-size: 26px; font-weight: 700; }
    .notif-title span { color: var(--muted); font-size: 14px; }
    .notif-list { display: flex; flex-direction: column; gap: 12px; }
    .notif-card { display: flex; align-items: flex-start; gap: 12px; padding: 16px; border: 1px solid var(--line); border-radius: var(--radius-surface); background: var(--surface); box-shadow: var(--shadow-surface); }
    .notif-icon { width: 40px; height: 40px; flex: 0 0 40px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-control); background: var(--brand-soft); color: var(--brand); }
    .notif-card h3 { margin: 0 0 4px; color: var(--ink); font-size: 16px; font-weight: 600; }
    .notif-card p { margin: 0 0 8px; color: var(--muted); font-size: 14px; }
    .notif-card .meta { color: var(--muted); font-size: 12px; }
    .empty-notifications { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 48px 20px; border: 1px dashed #bdcbc5; border-radius: var(--radius-surface); background: var(--surface); color: var(--muted); text-align: center; }
    .empty-notifications i { color: #9aa8a2; font-size: 32px; }
    .empty-notifications p { margin: 0; color: var(--ink); font-size: 14px; font-weight: 600; }

    @media (max-width: 640px) {
        .notif-header { align-items: flex-start; flex-direction: column; }
    }
</style>

<div class="notif-container">
    <div class="notif-header">
        <div class="notif-title">
            <h2>Notifications</h2>
            <span>{{ $notifications->count() }} total</span>
        </div>
        <a href="{{ route('requests.index') }}" class="btn-soft">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            View requests
        </a>
    </div>

    <div class="notif-list">
        @forelse($notifications as $notif)
            <div class="notif-card {{ $notif['updated_at']->greaterThan(session('notifications_last_viewed_at', now()->subYear())) ? 'ring-1 ring-emerald-200' : '' }}">
                <div class="notif-icon">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div class="flex-1">
                    <h3>{{ $notif['request_code'] ?? $notif['title'] ?? 'Request update' }}
                        @if($notif['updated_at']->greaterThan(session('notifications_last_viewed_at', now()->subYear())))
                            <span class="ml-2 px-1.5 py-0.5 text-[10px] font-bold text-white bg-emerald-500 rounded-xl align-middle">NEW</span>
                        @endif
                    </h3>
                    <p>{{ $notif['message'] }}</p>
                    <div class="meta">
                        <span>{{ $notif['updated_at']->format('M d, Y H:i') }}</span>
                        @if($notif['item_name'])
                            <span> • {{ $notif['item_name'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-notifications">
                <i class="fa-regular fa-bell-slash"></i>
                <div>
                    <p>No new notifications</p>
                    <span style="font-size: 12px; color: #a0aec0;">You haven't received any logistics requests or stock alerts yet.</span>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection