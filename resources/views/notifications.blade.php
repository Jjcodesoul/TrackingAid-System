@extends('layouts.app')

@section('content')
<style>
    .notif-container {
        display: flex;
        flex-direction: column;
        gap: 24px;
        width: 100%;
    }

    .notif-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 16px;
    }

    .notif-title h2 {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
    }

    .notif-title span {
        font-size: 14px;
        color: #718096;
    }

    .notif-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .notif-card {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 16px 18px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .notif-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0fdf4;
        color: #16a34a;
        flex-shrink: 0;
    }

    .notif-card h3 {
        margin: 0 0 4px;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }

    .notif-card p {
        margin: 0 0 8px;
        font-size: 13px;
        color: #475569;
    }

    .notif-card .meta {
        font-size: 12px;
        color: #64748b;
    }

    .empty-notifications {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        background: #ffffff;
        border-radius: 12px;
        border: 1px dashed #cbd5e1;
        color: #94a3b8;
        text-align: center;
        gap: 12px;
    }

    .empty-notifications i {
        font-size: 48px;
        color: #cbd5e1;
    }

    .empty-notifications p {
        font-size: 14px;
        font-weight: 500;
        margin: 0;
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
                            <span class="ml-2 px-1.5 py-0.5 text-[10px] font-bold text-white bg-emerald-500 rounded-none align-middle">NEW</span>
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