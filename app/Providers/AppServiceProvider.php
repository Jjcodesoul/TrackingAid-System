<?php

namespace App\Providers;

use App\Models\Request as SupplyRequest;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share unread notification count with the app layout so the
        // bell indicator and sidebar badge work on every authenticated page.
        View::composer('layouts.app', function ($view): void {
            $lastViewedAt = session('notifications_last_viewed_at');
            $lastViewedAt = $lastViewedAt ? Carbon::parse($lastViewedAt) : null;

            $unreadCount = app(NotificationService::class)->unreadCount($lastViewedAt);
            $requestsCount = SupplyRequest::where('status', 'Pending')->count();

            $view->with([
                'unreadCount' => $unreadCount,
                'requestsCount' => $requestsCount,
            ]);
        });
    }
}
