<?php

namespace App\Providers;

use App\Models\Request as SupplyRequest;
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
        View::composer('layouts.app', function ($view) {
            $lastViewedAt = session('notifications_last_viewed_at');

            $unreadCount = SupplyRequest::whereNotNull('notification_status')
                ->when($lastViewedAt, fn ($q) => $q->where('updated_at', '>', $lastViewedAt))
                ->count();

            $view->with('unreadCount', $unreadCount);
        });
    }
}

