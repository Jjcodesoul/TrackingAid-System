<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Request as SupplyRequest;
use App\Models\StockBatch;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NotificationService
{
    private const LOW_STOCK_THRESHOLD = 10;
    private const EXPIRING_WITHIN_DAYS = 30;

    public function all(?Carbon $lastViewedAt = null): Collection
    {
        return collect()
            ->merge($this->pendingRequestAlerts())
            ->merge($this->requestUpdateAlerts())
            ->merge($this->lowStockAlerts())
            ->merge($this->expiringItemAlerts())
            ->sortByDesc(fn (array $alert): int => $alert['updated_at']->getTimestamp())
            ->values()
            ->map(function (array $alert) use ($lastViewedAt): array {
                $alert['read'] = $lastViewedAt
                    ? $alert['updated_at']->lessThanOrEqualTo($lastViewedAt)
                    : false;

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

    public function lowStockAlerts(): Collection
    {
        return Item::with('stockBatches')
            ->get()
            ->filter(fn (Item $item): bool => $item->total_stock > 0 && $item->total_stock < self::LOW_STOCK_THRESHOLD)
            ->map(function (Item $item): array {
                return [
                    'id' => 'low-stock-' . $item->id,
                    'type' => 'low_stock',
                    'title' => 'Low stock: ' . $item->name,
                    'message' => $item->sku . ' has ' . number_format($item->total_stock) . ' units remaining.',
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

    private function pendingRequestAlerts(): Collection
    {
        return SupplyRequest::with('inventory')
            ->where('status', 'Pending')
            ->latest('updated_at')
            ->get()
            ->map(function (SupplyRequest $request): array {
                $itemLabel = $request->inventory?->name ?? 'requested item';

                return [
                    'id' => 'pending-request-' . $request->id,
                    'type' => 'pending_request',
                    'title' => 'Pending request ' . $request->request_code,
                    'message' => 'Awaiting review: ' . number_format($request->quantity) . ' ' . $itemLabel . '.',
                    'request_code' => $request->request_code,
                    'item_name' => $request->inventory?->name,
                    'url' => route('requests.index'),
                    'icon' => 'fa-regular fa-hourglass-half',
                    'tone' => 'info',
                    'updated_at' => Carbon::parse($request->updated_at ?? $request->created_at ?? now()),
                ];
            });
    }

    private function requestUpdateAlerts(): Collection
    {
        return SupplyRequest::with('inventory')
            ->whereNotNull('notification_status')
            ->latest('updated_at')
            ->get()
            ->map(function (SupplyRequest $request): array {
                return [
                    'id' => 'request-update-' . $request->id,
                    'type' => 'request_update',
                    'title' => $request->request_code ?? 'Request update',
                    'message' => $request->notification_status,
                    'request_code' => $request->request_code,
                    'item_name' => $request->inventory?->name,
                    'url' => route('requests.index'),
                    'icon' => 'fa-solid fa-bell',
                    'tone' => $this->requestTone($request->status),
                    'updated_at' => Carbon::parse($request->updated_at ?? now()),
                ];
            });
    }

    private function expiringAlertForItem(Item $item): ?array
    {
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

    private function requestTone(?string $status): string
    {
        return match ($status) {
            'Approved', 'Released' => 'success',
            'Rejected' => 'danger',
            default => 'info',
        };
    }
}
