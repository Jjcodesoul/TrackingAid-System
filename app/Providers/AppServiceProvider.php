<?php

namespace App\Providers;

use App\Models\Request as SupplyRequest;
use App\Models\ResqoperationForwardedRequest;
use App\Models\BorrowRelease;
use App\Models\Item;
use App\Models\ReturnItem;
use App\Models\StockBatch;
use App\Models\User;
use App\Observers\ActivityLogObserver;
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
        foreach ([User::class, Item::class, StockBatch::class, SupplyRequest::class, BorrowRelease::class, ReturnItem::class] as $model) {
            $model::observe(ActivityLogObserver::class);
        }

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