<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created', [
            'new' => $this->safeAttributes($model->getAttributes()),
        ]);
    }

    public function updated(Model $model): void
    {
        $changes = $this->safeAttributes($model->getChanges());

        if ($changes === []) {
            return;
        }

        $original = [];
        foreach (array_keys($changes) as $key) {
            $original[$key] = $model->getRawOriginal($key);
        }

        $this->record($model, 'updated', [
            'old' => $this->safeAttributes($original),
            'new' => $changes,
        ]);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', [
            'old' => $this->safeAttributes($model->getAttributes()),
        ]);
    }

    private function record(Model $model, string $eventType, array $properties): void
    {
        ActivityLog::create([
            'actor_id' => Auth::id(),
            'event_type' => $eventType,
            'subject_type' => $model::class,
            'subject_id' => (string) $model->getKey(),
            'properties' => $properties,
        ]);
    }

    private function safeAttributes(array $attributes): array
    {
        foreach (['password', 'remember_token', 'token', 'api_token', 'auth_version', 'last_login_at'] as $sensitiveKey) {
            unset($attributes[$sensitiveKey]);
        }

        return $attributes;
    }
}
