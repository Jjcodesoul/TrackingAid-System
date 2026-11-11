<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\BorrowRelease;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Request as SupplyRequest;
use App\Models\ReturnItem;
use App\Models\ResqoperationForwardedRequest;
use App\Models\StockBatch;
use App\Models\User;

class ActivityLogPresenter
{
    private array $subjectAttributes = [];

    private array $inventoryNames = [];

    private array $itemUnits = [];

    private const HIDDEN_FIELDS = [
        'id',
        'remember_token',
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'api_token',
        'access_token',
        'secret',
        'payload_json',
        'auth_version',
        'last_login_at',
        'idempotency_key',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    private const FIELD_LABELS = [
        'name' => 'Name',
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'email' => 'Email',
        'role' => 'Role',
        'status' => 'Status',
        'quantity' => 'Quantity',
        'approved_quantity' => 'Approved quantity',
        'condition' => 'Condition',
        'notes' => 'Remarks',
        'approval_notes' => 'Approval notes',
        'category' => 'Category',
        'unit_type' => 'Unit',
        'size_weight' => 'Size / weight',
        'target_beneficiary' => 'Beneficiary',
        'variant' => 'Variant',
        'type' => 'Item type',
        'sku' => 'SKU',
        'min_stock' => 'Minimum stock',
        'expiration_date' => 'Expiration date',
        'supplier' => 'Supplier',
        'date_received' => 'Date received',
        'movement_type' => 'Movement type',
        'storage_location' => 'Storage location',
        'location' => 'Destination',
        'purpose' => 'Purpose',
        'delivery_status' => 'Delivery status',
        'dispatched_at' => 'Dispatched at',
        'in_transit_at' => 'In transit at',
        'arrived_at' => 'Arrived at',
        'notification_status' => 'Notification status',
        'approved_at' => 'Approved at',
        'rejected_at' => 'Rejected at',
        'resqperation_status' => 'Status',
        'tracking_reference' => 'Tracking reference',
        'request_source' => 'Request source',
    ];

    public function present(ActivityLog $log): array
    {
        return [
            'log' => $log,
            'summary' => $this->summary($log),
            'module' => $this->moduleName($log),
            'record' => $this->recordLabel($log),
            'changes' => $this->changes($log),
        ];
    }

    private function forwardedRequestSummary(ActivityLog $log, string $action, array $attributes): string
    {
        $reference = $attributes['tracking_reference'] ?? '#'.$log->subject_id;
        $oldStatus = strtolower((string) ($log->properties['old']['resqperation_status'] ?? ''));
        $newStatus = strtolower((string) ($log->properties['new']['resqperation_status'] ?? ''));

        if ($log->event_type === 'created') {
            return 'Received forwarded request '.$reference;
        }
        if ($newStatus === 'approved' && $oldStatus !== 'approved') {
            return 'Approved forwarded request '.$reference;
        }
        if ($newStatus === 'rejected' && $oldStatus !== 'rejected') {
            return 'Rejected forwarded request '.$reference;
        }
        if ($newStatus !== '' && $oldStatus !== '' && $newStatus !== $oldStatus) {
            return "Updated forwarded request {$reference} from {$oldStatus} to {$newStatus}";
        }

        return ucfirst($action).' forwarded request '.$reference;
    }

    public function presentMany(iterable $logs): array
    {
        $logs = collect($logs);
        $subjectTypes = [
            User::class,
            Item::class,
            StockBatch::class,
            SupplyRequest::class,
            ResqoperationForwardedRequest::class,
            BorrowRelease::class,
            ReturnItem::class,
        ];
        $updatedByType = $logs
            ->filter(fn (ActivityLog $log): bool => $log->event_type === 'updated' && in_array($log->subject_type, $subjectTypes, true))
            ->groupBy('subject_type');

        foreach ($updatedByType as $subjectType => $subjectLogs) {
            $ids = $subjectLogs->pluck('subject_id')->unique()->values();
            $query = $subjectType === Item::class ? Item::withTrashed() : $subjectType::query();

            foreach ($ids as $id) {
                $this->subjectAttributes[$subjectType][(string) $id] = [];
            }

            foreach ($query->whereIn('id', $ids)->get() as $subject) {
                $this->subjectAttributes[$subjectType][(string) $subject->getKey()] = $subject->getAttributes();
            }
        }

        $attributes = $logs->flatMap(function (ActivityLog $log): array {
            $subjectAttributes = $this->subjectAttributes[$log->subject_type][(string) $log->subject_id] ?? [];

            return [
                $subjectAttributes,
                $log->properties['old'] ?? [],
                $log->properties['new'] ?? [],
            ];
        });

        $inventoryIds = $attributes->pluck('inventory_id')->filter()->unique()->values();
        foreach (Inventory::withTrashed()->whereIn('id', $inventoryIds)->get(['id', 'name', 'sku']) as $inventory) {
            $this->inventoryNames[(string) $inventory->id] = trim($inventory->name.($inventory->sku ? " (SKU {$inventory->sku})" : ''));
        }
        foreach ($inventoryIds as $inventoryId) {
            $this->inventoryNames[(string) $inventoryId] ??= 'inventory item #'.$inventoryId;
        }

        $itemIds = $attributes->pluck('item_id')->filter()->unique()->values();
        foreach (Item::withTrashed()->whereIn('id', $itemIds)->get(['id', 'unit_type']) as $item) {
            $this->itemUnits[(string) $item->id] = $item->unit_type ?: 'units';
        }
        foreach ($itemIds as $itemId) {
            $this->itemUnits[(string) $itemId] ??= 'units';
        }

        return $logs->map(fn (ActivityLog $log): array => $this->present($log))->all();
    }

    public function recordLabel(ActivityLog $log): string
    {
        if (in_array($log->event_type, ['logged_in', 'logged_out'], true)) {
            return 'Authentication for '.($log->actor?->name ?? 'User #'.$log->subject_id);
        }
        if ($log->event_type === 'csv_imported') {
            return 'CSV inventory import ('.($log->properties['new']['item_count'] ?? 0).' items)';
        }

        return match ($log->subject_type) {
            BorrowRelease::class => 'Borrow/Release #'.$log->subject_id,
            SupplyRequest::class => 'Request '.($log->properties['new']['request_code'] ?? $log->properties['old']['request_code'] ?? '#'.$log->subject_id),
            ResqoperationForwardedRequest::class => 'Forwarded request '.($log->properties['new']['tracking_reference'] ?? $log->properties['old']['tracking_reference'] ?? '#'.$log->subject_id),
            ReturnItem::class => 'Return #'.$log->subject_id,
            StockBatch::class => 'Stock-In #'.$log->subject_id,
            Item::class => 'Inventory Item '.$this->itemName($log->properties['new'] ?? $log->properties['old'] ?? []),
            User::class => 'User Account '.$this->userName($log->properties['new'] ?? $log->properties['old'] ?? []),
            default => class_basename($log->subject_type).' #'.$log->subject_id,
        };
    }

    private function summary(ActivityLog $log): string
    {
        if ($log->event_type === 'csv_imported') {
            return 'Imported '.($log->properties['new']['item_count'] ?? 0).' inventory item(s) from CSV';
        }

        if ($log->event_type === 'logged_in') {
            return 'User signed in: '.($log->actor?->name ?? 'User #'.$log->subject_id);
        }

        if ($log->event_type === 'logged_out') {
            return 'User signed out: '.($log->actor?->name ?? 'User #'.$log->subject_id);
        }

        if ($log->event_type === 'security_action') {
            return (string) ($log->properties['action'] ?? 'Security action performed for user account');
        }

        if ($log->event_type === 'password_reset_link_sent') {
            return 'Sent a password reset link for user account';
        }

        $attributes = array_merge(
            $this->subjectAttributes($log),
            $log->properties['new'] ?? $log->properties['old'] ?? []
        );
        $subject = $this->subjectName($log, $attributes);
        if ($log->subject_type === User::class
            && array_key_exists('is_active', $log->properties['old'] ?? [])
            && array_key_exists('is_active', $log->properties['new'] ?? [])
            && (bool) $log->properties['old']['is_active'] !== (bool) $log->properties['new']['is_active']) {
            return ((bool) $log->properties['new']['is_active'] ? 'Activated' : 'Deactivated').' account: '.$subject;
        }

        $action = match ($log->event_type) {
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'removed',
            default => str_replace('_', ' ', $log->event_type),
        };

        return match ($log->subject_type) {
            User::class => ucfirst($action).' user account: '.$subject,
            Item::class => ucfirst($action).' inventory item: '.$subject,
            StockBatch::class => $this->stockBatchSummary($log, $attributes, $action, $subject),
            SupplyRequest::class => $this->requestSummary($log, $attributes, $action, $subject),
            ResqoperationForwardedRequest::class => $this->forwardedRequestSummary($log, $action, $attributes),
            BorrowRelease::class => $this->releaseSummary($log, $attributes, $action, $subject),
            ReturnItem::class => $this->returnSummary($log, $attributes, $action, $subject),
            default => ucfirst($action).' '.class_basename($log->subject_type).' #'.$log->subject_id,
        };
    }

    private function moduleName(ActivityLog $log): string
    {
        if (in_array($log->event_type, ['logged_in', 'logged_out'], true)) {
            return 'Authentication';
        }

        return match ($log->subject_type) {
            User::class => 'User Accounts',
            Item::class => 'Inventory',
            StockBatch::class => 'Stock-In',
            SupplyRequest::class => 'Supply Requests',
            ResqoperationForwardedRequest::class => 'Supply Requests',
            BorrowRelease::class => 'Borrow / Release',
            ReturnItem::class => 'Returns',
            default => class_basename($log->subject_type),
        };
    }

    private function stockBatchSummary(ActivityLog $log, array $attributes, string $action, string $subject): string
    {
        if ($log->event_type === 'created') {
            return sprintf(
                'Recorded stock-in of %s %s for %s',
                $attributes['quantity'] ?? 'an unspecified quantity',
                $this->unitForItem($attributes['item_id'] ?? null),
                $subject
            );
        }

        return ucfirst($action).' stock batch for '.$subject;
    }

    private function requestSummary(ActivityLog $log, array $attributes, string $action, string $subject): string
    {
        if ($log->event_type === 'created') {
            return sprintf(
                'Submitted request %s for %s %s',
                $attributes['request_code'] ?? '#'.$log->subject_id,
                $attributes['quantity'] ?? 'an unspecified quantity',
                $subject
            );
        }

        $oldStatus = $log->properties['old']['status'] ?? null;
        $newStatus = $log->properties['new']['status'] ?? null;

        if ($newStatus === 'Approved') {
            return sprintf('Approved request for %s%s', $subject, $this->quantitySuffix($attributes['approved_quantity'] ?? null));
        }

        if ($newStatus === 'Rejected') {
            return 'Rejected request for '.$subject;
        }

        if ($oldStatus && $newStatus && $oldStatus !== $newStatus) {
            return sprintf('Changed request status from %s to %s for %s', $oldStatus, $newStatus, $subject);
        }

        return ucfirst($action).' request '.($attributes['request_code'] ?? '#'.$log->subject_id).' for '.$subject;
    }

    private function releaseSummary(ActivityLog $log, array $attributes, string $action, string $subject): string
    {
        if ($log->event_type === 'created') {
            return sprintf(
                'Released %s %s of %s%s',
                $attributes['quantity'] ?? 'an unspecified quantity',
                $attributes['unit'] ?? $this->unitForItem($attributes['inventory_id'] ?? null),
                $subject,
                filled($attributes['location'] ?? null) ? ' to '.trim((string) $attributes['location']) : ''
            );
        }

        $oldStatus = $log->properties['old']['delivery_status'] ?? null;
        $newStatus = $log->properties['new']['delivery_status'] ?? null;

        if ($oldStatus && $newStatus && $oldStatus !== $newStatus) {
            return sprintf('Updated delivery status from %s to %s for %s', $oldStatus, $newStatus, $subject);
        }

        return ucfirst($action).' release of '.$subject;
    }

    private function returnSummary(ActivityLog $log, array $attributes, string $action, string $subject): string
    {
        if ($log->event_type === 'created') {
            $condition = filled($attributes['condition'] ?? null) ? ' ('.strtolower((string) $attributes['condition']).')' : '';

            return sprintf(
                'Recorded return of %s %s of %s%s',
                $attributes['quantity'] ?? 'an unspecified quantity',
                $this->unitForItem($attributes['inventory_id'] ?? null),
                $subject,
                $condition
            );
        }

        return ucfirst($action).' return for '.$subject;
    }

    private function changes(ActivityLog $log): array
    {
        $properties = $log->properties ?? [];

        if ($log->event_type === 'updated') {
            $old = $properties['old'] ?? [];
            $new = $properties['new'] ?? [];
            $fields = array_intersect(array_keys($new), array_keys($old));

            return collect($fields)
                ->filter(fn (string $field): bool => ! $this->isHidden($field) && $this->valuesDiffer($old[$field], $new[$field]))
                ->map(fn (string $field): array => [
                    'label' => $this->fieldLabel($field),
                    'old' => $this->formatValue($field, $old[$field]),
                    'new' => $this->formatValue($field, $new[$field]),
                ])
                ->values()
                ->all();
        }

        $values = $properties[$log->event_type === 'deleted' ? 'old' : 'new'] ?? [];

        return collect($values)
            ->reject(fn ($value, $field): bool => $this->isHidden((string) $field) || str_ends_with((string) $field, '_id'))
            ->map(fn ($value, $field): array => [
                'label' => $this->fieldLabel((string) $field),
                'value' => $this->formatValue((string) $field, $value),
            ])
            ->values()
            ->all();
    }

    private function subjectName(ActivityLog $log, array $attributes): string
    {
        return match ($log->subject_type) {
            User::class => $this->userName($attributes),
            Item::class => $this->itemName($attributes),
            StockBatch::class, ReturnItem::class => $this->inventoryName($attributes['inventory_id'] ?? null),
            SupplyRequest::class => $this->inventoryName($attributes['inventory_id'] ?? null),
            ResqoperationForwardedRequest::class => $attributes['item_name'] ?? $attributes['resource_type'] ?? 'Forwarded request',
            BorrowRelease::class => $this->inventoryName($attributes['inventory_id'] ?? null),
            default => class_basename($log->subject_type).' #'.$log->subject_id,
        };
    }

    private function subjectAttributes(ActivityLog $log): array
    {
        if ($log->event_type !== 'updated') {
            return [];
        }

        if (array_key_exists((string) $log->subject_id, $this->subjectAttributes[$log->subject_type] ?? [])) {
            return $this->subjectAttributes[$log->subject_type][(string) $log->subject_id];
        }

        $subjectTypes = [
            User::class,
            Item::class,
            StockBatch::class,
            SupplyRequest::class,
            ResqoperationForwardedRequest::class,
            BorrowRelease::class,
            ReturnItem::class,
        ];

        if (! in_array($log->subject_type, $subjectTypes, true)) {
            return [];
        }

        $query = in_array($log->subject_type, [Item::class], true)
            ? Item::withTrashed()
            : $log->subject_type::query();

        $subject = $query->find($log->subject_id);

        return $this->subjectAttributes[$log->subject_type][(string) $log->subject_id] = $subject?->getAttributes() ?? [];
    }

    private function userName(array $attributes): string
    {
        $name = trim(implode(' ', array_filter([
            $attributes['first_name'] ?? null,
            $attributes['last_name'] ?? null,
        ])));

        return $name ?: ($attributes['name'] ?? $attributes['email'] ?? 'Unknown user');
    }

    private function itemName(array $attributes): string
    {
        $name = $attributes['name'] ?? 'Unknown item';

        return filled($attributes['sku'] ?? null) ? "{$name} (SKU {$attributes['sku']})" : $name;
    }

    private function inventoryName(mixed $inventoryId): string
    {
        if (! $inventoryId) {
            return 'an item';
        }

        $inventoryId = (string) $inventoryId;

        if (! array_key_exists($inventoryId, $this->inventoryNames)) {
            $inventory = Inventory::withTrashed()->find($inventoryId);
            $this->inventoryNames[$inventoryId] = $inventory
                ? trim($inventory->name.($inventory->sku ? " (SKU {$inventory->sku})" : ''))
                : 'inventory item #'.$inventoryId;
        }

        return $this->inventoryNames[$inventoryId];
    }

    private function unitForItem(mixed $itemId): string
    {
        if (! $itemId) {
            return 'units';
        }

        $itemId = (string) $itemId;

        if (! array_key_exists($itemId, $this->itemUnits)) {
            $this->itemUnits[$itemId] = Item::withTrashed()->find($itemId)?->unit_type ?: 'units';
        }

        return $this->itemUnits[$itemId];
    }

    private function fieldLabel(string $field): string
    {
        if (isset(self::FIELD_LABELS[$field])) {
            return self::FIELD_LABELS[$field];
        }

        return ucfirst(str_replace('_', ' ', $field));
    }

    private function quantitySuffix(mixed $quantity): string
    {
        return $quantity === null || $quantity === ''
            ? ''
            : ' for '.$quantity.' units';
    }

    private function formatValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'Not set';
        }

        if (in_array($field, ['inventory_id'], true)) {
            return $this->inventoryName($value);
        }

        if ($field === 'item_id') {
            $item = Item::withTrashed()->find($value);

            return $item ? $this->itemName($item->getAttributes()) : 'Item #'.$value;
        }

        if (in_array($field, ['processed_by_user_id', 'approved_by_user_id', 'created_by_user_id', 'delivery_personnel_id'], true)) {
            return User::find($value)?->name ?? 'User #'.$value;
        }

        if ($field === 'request_id') {
            return SupplyRequest::find($value)?->request_code ?? 'Request #'.$value;
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return implode(', ', array_map(
                fn ($key, $item): string => $this->fieldLabel((string) $key).': '.$this->formatValue((string) $key, $item),
                array_keys($value),
                array_values($value)
            ));
        }

        if (in_array($field, ['role', 'type', 'movement_type', 'condition', 'delivery_status', 'status'], true)) {
            return ucfirst(str_replace('_', ' ', (string) $value));
        }

        return (string) $value;
    }

    private function isHidden(string $field): bool
    {
        return in_array($field, self::HIDDEN_FIELDS, true)
            || str_ends_with($field, '_token')
            || str_ends_with($field, '_key');
    }

    private function valuesDiffer(mixed $old, mixed $new): bool
    {
        return (string) ($old ?? '') !== (string) ($new ?? '');
    }
}
