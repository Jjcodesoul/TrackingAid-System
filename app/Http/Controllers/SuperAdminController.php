<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BorrowRelease;
use App\Models\Item;
use App\Models\Inventory;
use App\Models\Request as SupplyRequest;
use App\Models\ReturnItem;
use App\Models\ResqoperationForwardedRequest;
use App\Models\StockBatch;
use App\Models\StorageLocation;
use App\Models\UserLoginEvent;
use App\Models\User;
use App\Services\ActivityLogPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    private const ACTIVITY_MODULES = [
        'accounts' => User::class,
        'inventory' => Item::class,
        'stock' => StockBatch::class,
        'requests' => [SupplyRequest::class, ResqoperationForwardedRequest::class],
        'borrow_release' => BorrowRelease::class,
        'returns' => ReturnItem::class,
        'storage' => StorageLocation::class,
    ];

    private const ACTIVITY_TYPES = [
        'created',
        'updated',
        'deleted',
        'csv_imported',
        'security_action',
        'password_reset_link_sent',
        'logged_in',
        'logged_out',
    ];

    public function dashboard(ActivityLogPresenter $presenter, \App\Http\Controllers\DashboardController $dashboardController): View
    {
        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count')
            ->groupBy('role')
            ->orderBy('role')
            ->get();

        return view('super-admin.dashboard', [
            ...$dashboardController->dashboardData(),
            'adminCount' => User::where('role', 'admin')->count(),
            'userCount' => User::count(),
            'activeUserCount' => User::where('is_active', true)->count(),
            'inactiveUserCount' => User::where('is_active', false)->count(),
            'roleCounts' => $roleCounts,
            'recentUsers' => User::query()
                ->orderByRaw('CASE WHEN last_login_at IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('last_login_at')
                ->limit(8)
                ->get(),
            'recentLogs' => $presenter->presentMany(ActivityLog::with('actor')->latest()->take(10)->get()),
            'recentInventoryLogs' => $presenter->presentMany(
                ActivityLog::with('actor')
                    ->whereIn('subject_type', [Item::class, StockBatch::class])
                    ->latest()
                    ->take(6)
                    ->get()
            ),
            'recentRequestLogs' => $presenter->presentMany(
                ActivityLog::with('actor')
                    ->whereIn('subject_type', self::ACTIVITY_MODULES['requests'])
                    ->latest()
                    ->take(6)
                    ->get()
            ),
        ]);
    }

    public function activityLogs(Request $request, ActivityLogPresenter $presenter): View
    {
        $validated = $request->validate([
            'tab' => ['nullable', 'in:recent,users'],
            'event_type' => ['nullable', 'in:'.implode(',', self::ACTIVITY_TYPES)],
            'search' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'role' => ['nullable', 'in:super_admin,admin,user,staff,delivery_personnel'],
            'module' => ['nullable', 'in:'.implode(',', [...array_keys(self::ACTIVITY_MODULES), 'authentication'])],
            'is_active' => ['nullable', 'in:0,1'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $tab = $validated['tab'] ?? 'recent';

        if ($tab === 'users') {
            $activity = ActivityLog::query()
                ->selectRaw('actor_id, COUNT(*) as activity_count, MAX(created_at) as last_activity')
                ->whereNotNull('actor_id')
                ->groupBy('actor_id');

            $usersQuery = User::query()
                ->leftJoinSub($activity, 'user_activity', 'user_activity.actor_id', '=', 'users.id')
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.role',
                    'users.is_active',
                    'users.last_login_at',
                    DB::raw('COALESCE(user_activity.activity_count, 0) as activity_count'),
                    'user_activity.last_activity',
                ]);

            if (Auth::user()->role !== 'super_admin') {
                $usersQuery->where('users.role', '!=', 'super_admin');
            }

            if (! empty($validated['search'])) {
                $search = $validated['search'];
                $usersQuery->where(function ($query) use ($search): void {
                    $query->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%")
                        ->orWhere('users.role', 'like', "%{$search}%");
                });
            }
            if (! empty($validated['role'])) {
                $usersQuery->where('users.role', $validated['role']);
            }
            if (array_key_exists('is_active', $validated)) {
                $usersQuery->where('users.is_active', (bool) $validated['is_active']);
            }

            return view('super-admin.activity-logs', [
                'tab' => $tab,
                'users' => $usersQuery
                    ->orderByDesc('last_activity')
                    ->orderBy('users.name')
                    ->paginate(25)
                    ->withQueryString(),
                'filters' => $validated,
                'filterUsers' => User::orderBy('name')->get(['id', 'name', 'role']),
                'activityTypes' => self::ACTIVITY_TYPES,
                'modules' => self::ACTIVITY_MODULES,
            ]);
        }

        $logsQuery = ActivityLog::with('actor')->latest();

        if (! empty($validated['event_type'])) {
            $logsQuery->where('event_type', $validated['event_type']);
        }

        if (! empty($validated['user_id'])) {
            $logsQuery->where('actor_id', $validated['user_id']);
        }
        if (! empty($validated['role'])) {
            $logsQuery->whereHas('actor', fn ($query) => $query->where('role', $validated['role']));
        }
        $this->applyModuleFilter($logsQuery, $validated['module'] ?? null);

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $logsQuery->where(function ($query) use ($search): void {
                $query->where('subject_type', 'like', "%{$search}%")
                    ->orWhere('subject_id', 'like', "%{$search}%")
                    ->orWhereHas('actor', function ($actorQuery) use ($search): void {
                        $actorQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($validated['date_from'])) {
            $logsQuery->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (! empty($validated['date_to'])) {
            $logsQuery->whereDate('created_at', '<=', $validated['date_to']);
        }

        $logs = $logsQuery->paginate(25)->withQueryString();
        $logs->setCollection(collect($presenter->presentMany($logs->getCollection())));

        return view('super-admin.activity-logs', [
            'tab' => $tab,
            'logs' => $logs,
            'filters' => $validated,
            'filterUsers' => User::orderBy('name')->get(['id', 'name', 'role']),
            'activityTypes' => self::ACTIVITY_TYPES,
            'modules' => self::ACTIVITY_MODULES,
        ]);
    }

    public function userActivity(Request $request, User $user, ActivityLogPresenter $presenter): View
    {
        $validated = $request->validate([
            'event_type' => ['nullable', 'in:'.implode(',', self::ACTIVITY_TYPES)],
            'role' => ['nullable', 'in:super_admin,admin,user,staff,delivery_personnel'],
            'module' => ['nullable', 'in:'.implode(',', [...array_keys(self::ACTIVITY_MODULES), 'authentication'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $userActivity = fn () => ActivityLog::with('actor')->where(function ($query) use ($user): void {
            $query->where('actor_id', $user->id)
                ->orWhere(function ($affectedAccount) use ($user): void {
                    $affectedAccount->where('subject_type', User::class)
                        ->where('subject_id', (string) $user->id);
                });
        });
        $logsQuery = $userActivity()->latest();

        if (! empty($validated['event_type'])) {
            $logsQuery->where('event_type', $validated['event_type']);
        }
        if (! empty($validated['role'])) {
            $logsQuery->whereHas('actor', fn ($query) => $query->where('role', $validated['role']));
        }
        $this->applyModuleFilter($logsQuery, $validated['module'] ?? null);
        if (! empty($validated['date_from'])) {
            $logsQuery->whereDate('created_at', '>=', $validated['date_from']);
        }
        if (! empty($validated['date_to'])) {
            $logsQuery->whereDate('created_at', '<=', $validated['date_to']);
        }

        $today = now()->toDateString();
        $logs = $logsQuery->paginate(20)->withQueryString();
        $logs->setCollection(collect($presenter->presentMany($logs->getCollection())));
        $loginEventsQuery = UserLoginEvent::where('user_id', $user->id)->latest();
        if (! empty($validated['date_from'])) {
            $loginEventsQuery->whereDate('created_at', '>=', $validated['date_from']);
        }
        if (! empty($validated['date_to'])) {
            $loginEventsQuery->whereDate('created_at', '<=', $validated['date_to']);
        }

        return view('super-admin.user-activity', [
            'userItem' => $user,
            'totalActivity' => $userActivity()->count(),
            'todayActivity' => $userActivity()->whereDate('created_at', $today)->count(),
            'lastActivity' => $userActivity()->max('created_at'),
            'logs' => $logs,
            'loginEvents' => $loginEventsQuery->paginate(15, ['*'], 'login_page')->withQueryString(),
            'filters' => $validated,
            'activityTypes' => self::ACTIVITY_TYPES,
            'modules' => self::ACTIVITY_MODULES,
            'roleOptions' => ['super_admin', 'admin', 'staff', 'user', 'delivery_personnel'],
        ]);
    }

    public function setAccountStatus(Request $request, User $user)
    {
        abort_if($user->role === 'super_admin', 403, 'Super Admin accounts cannot be managed here.');

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $activate = (bool) $validated['is_active'];

        DB::transaction(function () use ($user, $activate): void {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->is_active === $activate) {
                return;
            }

            $lockedUser->is_active = $activate;
            if (! $activate) {
                $lockedUser->auth_version++;
            }
            $lockedUser->save();

            if (! $activate) {
                $lockedUser->tokens()->delete();
            }
        });

        return back()->with('success', $activate ? 'Account activated.' : 'Account deactivated and sessions revoked.');
    }

    public function forceLogout(User $user)
    {
        abort_if($user->role === 'super_admin', 403, 'Super Admin sessions cannot be revoked here.');

        DB::transaction(function () use ($user): void {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $lockedUser->auth_version++;
            $lockedUser->save();
            $lockedUser->tokens()->delete();

            ActivityLog::create([
                'actor_id' => Auth::id(),
                'event_type' => 'security_action',
                'subject_type' => User::class,
                'subject_id' => (string) $lockedUser->id,
                'properties' => ['action' => 'Forced logout of all sessions and API tokens'],
            ]);
        });

        return back()->with('success', 'All sessions and API tokens for this account have been revoked.');
    }

    public function sendPasswordReset(User $user)
    {
        abort_if($user->role === 'super_admin', 403, 'Super Admin passwords cannot be reset here.');

        $status = Password::sendResetLink(['email' => $user->email]);
        if ($status !== Password::RESET_LINK_SENT) {
            return back()->with('error', __($status));
        }

        DB::transaction(function () use ($user): void {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $lockedUser->auth_version++;
            $lockedUser->save();
            $lockedUser->tokens()->delete();

            ActivityLog::create([
                'actor_id' => Auth::id(),
                'event_type' => 'password_reset_link_sent',
                'subject_type' => User::class,
                'subject_id' => (string) $lockedUser->id,
                'properties' => ['action' => 'Sent password reset link'],
            ]);
        });

        return back()->with('success', 'Password reset link sent to '.$user->email.'. Existing sessions have been revoked.');
    }

    private function applyModuleFilter($query, ?string $module): void
    {
        if ($module === 'authentication') {
            $query->whereIn('event_type', ['logged_in', 'logged_out']);
        } elseif ($module !== null && isset(self::ACTIVITY_MODULES[$module])) {
            $subjectTypes = self::ACTIVITY_MODULES[$module];
            $query->whereIn('subject_type', is_array($subjectTypes) ? $subjectTypes : [$subjectTypes]);
        }
    }
}
