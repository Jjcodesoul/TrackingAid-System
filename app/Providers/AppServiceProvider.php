<?php

namespace App\Providers;

use App\Models\Request as SupplyRequest;
use App\Models\ResqoperationForwardedRequest;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $lastViewedAt = session('notifications_last_viewed_at');
            $lastViewedAt = $lastViewedAt
                ? Carbon::parse($lastViewedAt)
                : null;

            $unreadCount = app(NotificationService::class)
                ->unreadCount($lastViewedAt);

            $requestsCount = SupplyRequest::where('status', 'Pending')->count()
                + ResqoperationForwardedRequest::whereIn(
                    'resqperation_status',
                    ['forwarded', 'Forwarded', 'pending', 'Pending']
                )->count();

            $view->with([
                'unreadCount' => $unreadCount,
                'requestsCount' => $requestsCount,
            ]);
        });
    }
}