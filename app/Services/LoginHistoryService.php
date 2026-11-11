<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserLoginEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginHistoryService
{
    public function record(?User $user, string $email, bool $succeeded, Request $request): void
    {
        DB::transaction(function () use ($user, $email, $succeeded, $request): void {
            if ($succeeded && $user) {
                $user->forceFill(['last_login_at' => now()])->saveQuietly();

                ActivityLog::create([
                    'actor_id' => $user->id,
                    'event_type' => 'logged_in',
                    'subject_type' => User::class,
                    'subject_id' => (string) $user->id,
                    'properties' => ['action' => 'User signed in'],
                ]);
            }

            UserLoginEvent::create([
                'user_id' => $user?->id,
                'email' => mb_substr(mb_strtolower(trim($email)), 0, 255),
                'succeeded' => $succeeded,
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 65535),
            ]);
        });
    }
}
