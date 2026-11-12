<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockBatch;
use App\Models\User;
use App\Notifications\LogisticsActivityNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    private const LOW_STOCK_THRESHOLD = 10;
    private const EXPIRING_WITHIN_DAYS = 30;

    public function all(?Carbon $lastViewedAt = null): Collection
    {
        $databaseAlerts = Auth::user()?->notifications()
            ->latest()
            ->get()
            ->map(function ($notification): array {
                return [
                    ...$notification->data,
                    'id' => $notification->id,
                    'url' => route('notifications.open', $notification->id),
                    'updated_at' => $notification->created_at,
                    'read' => $notification->read_at !== null,
                ];
            }) ?? collect();

        return collect()
            ->merge($databaseAlerts)
            ->merge($this->lowStockAlerts())
            ->merge($this->expiringItemAlerts())
            ->sortByDesc(fn (array $alert): int => $alert['updated_at']->getTimestamp())
            ->values()
            ->map(function (array $alert) use ($lastViewedAt): array {
                if (! array_key_exists('read', $alert)) {
                    $alert['read'] = $lastViewedAt
                    ? $alert['updated_at']->lessThanOrEqualTo($lastViewedAt)
                    : false;
                }

                return $alert;
            });
    }

    public function unread(?Carbon $lastViewedAt = null): Collection
    {
        return $this->all($lastViewedAt)
            ->reject(fn (array $alert): bool => $alert['read'])
            ->values();
    }

    public function unreadCount(?Carbon $lastViewedAt = null): int
    {
        return $this->unread($lastViewedAt)->count();
    }

    public function notifyRoles(
        array $roles,
        string $title,
        string $message,
        string $url,
        string $type,
        ?string $requestCode = null,
        ?string $itemName = null,
    ): void {
        User::query()
            ->whereIn('role', $roles)
            ->where('is_active', true)
            ->get()
            ->each(fn (User $user) => $user->notify(new LogisticsActivityNotification([
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'request_code' => $requestCode,
                'item_name' => $itemName,
            ])));
    }

    public function lowStockAlerts(): Collection
    {
        return Item::with('stockBatches')
            ->get()
            ->filter(function (Item $item): bool {
                $threshold = (int) ($item->min_stock ?? self::LOW_STOCK_THRESHOLD);
                return $item->total_stock > 0 && $item->total_stock < $threshold;
            })
            ->map(function (Item $item): array {
                $threshold = (int) ($item->min_stock ?? self::LOW_STOCK_THRESHOLD);
                return [
                    'id' => 'low-stock-' . $item->id,
                    'type' => 'low_stock',
                    'title' => 'Low stock: ' . $item->name,
                    'message' => $item->sku . ' has ' . number_format($item->total_stock) . ' units remaining (min: ' . number_format($threshold) . ').',
                    'request_code' => null,
                    'item_name' => $item->name,
                    'url' => route('inventory.index'),
                    'icon' => 'fa-solid fa-triangle-exclamation',
                    'tone' => 'warning',
                    'updated_at' => $this->latestItemActivity($item),
                ];
            })
            ->values();
    }

    public function expiringItemAlerts(): Collection
    {
        return Item::with('stockBatches')
            ->get()
            ->map(fn (Item $item): ?array => $this->expiringAlertForItem($item))
            ->filter()
            ->values();
    }

    private function expiringAlertForItem(Item $item): ?array
    {

        if ($item->total_stock <= 0) {
            return null;
        }

        $batch = $item->stockBatches
            ->filter(fn (StockBatch $batch): bool => (int) $batch->quantity > 0 && filled($batch->expiration_date))
            ->sortBy(fn (StockBatch $batch): string => Carbon::parse($batch->expiration_date)->toDateString())
            ->first();

        $expirationDate = $batch?->expiration_date
            ? Carbon::parse($batch->expiration_date)
            : ($item->expiration_date ? Carbon::parse($item->expiration_date) : null);

        if (! $expirationDate || $expirationDate->greaterThan(now()->addDays(self::EXPIRING_WITHIN_DAYS))) {
            return null;
        }

        $quantity = $batch ? (int) $batch->quantity : $item->total_stock;
        $isExpired = $expirationDate->isPast();
        $dateLabel = $expirationDate->format('M d, Y');

        return [
            'id' => 'expiring-item-' . $item->id,
            'type' => 'expiring_item',
            'title' => ($isExpired ? 'Expired item: ' : 'Expiring item: ') . $item->name,
            'message' => ($isExpired ? 'Expired on ' : 'Expires on ') . $dateLabel . ': ' . $item->sku . ' has ' . number_format($quantity) . ' affected units.',
            'request_code' => null,
            'item_name' => $item->name,
            'url' => route('inventory.index'),
            'icon' => 'fa-regular fa-clock',
            'tone' => $isExpired ? 'danger' : 'warning',
            'updated_at' => $this->expiringAlertTime($item, $batch, $expirationDate),
        ];
    }

    private function latestItemActivity(Item $item): Carbon
    {
        $batchUpdatedAt = $item->stockBatches
            ->pluck('updated_at')
            ->filter()
            ->map(fn ($date): Carbon => Carbon::parse($date))
            ->sortDesc()
            ->first();

        $itemUpdatedAt = Carbon::parse($item->updated_at ?? now());

        return $batchUpdatedAt && $batchUpdatedAt->greaterThan($itemUpdatedAt)
            ? $batchUpdatedAt
            : $itemUpdatedAt;
    }

    private function expiringAlertTime(Item $item, ?StockBatch $batch, Carbon $expirationDate): Carbon
    {
        $thresholdTime = $expirationDate->copy()->subDays(self::EXPIRING_WITHIN_DAYS)->startOfDay();
        $activityTime = $batch?->updated_at
            ? Carbon::parse($batch->updated_at)
            : $this->latestItemActivity($item);

        return $activityTime->greaterThan($thresholdTime) ? $activityTime : $thresholdTime;
    }

}
