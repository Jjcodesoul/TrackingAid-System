<?php

use App\Http\Controllers\BorrowReleaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureActiveAccount;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Support\Facades\Route;

// 1. Landing Root Route
Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'super_admin' => redirect()->route('super-admin.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }
    return redirect('/login');
});

// All authenticated routes (Admin + User/Staff)
Route::middleware(['auth', EnsureActiveAccount::class])->group(function () {

    // 2. Regular User Dashboard
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'super_admin') {
            return redirect()->route('super-admin.dashboard');
        }
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return app(DashboardController::class)->index();
    })->name('dashboard');

    Route::middleware(SuperAdminMiddleware::class)->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/activity-logs', [SuperAdminController::class, 'activityLogs'])->name('activity-logs');
        Route::get('/activity-logs/users/{user}', [SuperAdminController::class, 'userActivity'])->name('activity-logs.users.show');
        Route::post('/users/{user}/status', [SuperAdminController::class, 'setAccountStatus'])->name('users.status');
        Route::post('/users/{user}/force-logout', [SuperAdminController::class, 'forceLogout'])->name('users.force-logout');
        Route::post('/users/{user}/password-reset', [SuperAdminController::class, 'sendPasswordReset'])->name('users.password-reset');
    });


    // 3. 🛑 ADMIN-ONLY CONTROL PANEL ROUTES
    Route::middleware(AdminMiddleware::class)->group(function () {
        
        Route::get('/admin/dashboard', function () {
            if (! in_array(auth()->user()->role, ['admin', 'super_admin'], true)) {
                return redirect()->route('dashboard')->with('error', 'Access denied.');
            }
            return app(DashboardController::class)->adminIndex();
        })->name('admin.dashboard');

        // Admin Only Inventory Actions
        Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/inventory/edit/{id}', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::post('/inventory/update/{id}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::post('/inventory/delete/{id}', [InventoryController::class, 'delete'])->name('inventory.delete');
        Route::post('/inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
        Route::post('/inventory/locations', [InventoryController::class, 'storeLocation'])->name('inventory.locations.store');

        Route::get('/stock-in', [StockController::class, 'create'])->name('stock.create');
        Route::post('/stock-in/store', [StockController::class, 'store'])->name('stock.store');

        Route::post('/requests/{request}/approve', [RequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{request}/reject', [RequestController::class, 'reject'])->name('requests.reject');
        Route::post('/requests/forwarded/{forwardedRequest}/approve', [RequestController::class, 'approveForwarded'])->name('requests.forwarded.approve');
        Route::post('/requests/forwarded/{forwardedRequest}/reject', [RequestController::class, 'rejectForwarded'])->name('requests.forwarded.reject');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

        // 👥 User Management Actions (Fixed to work perfectly with standard browser form submissions)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/users/update/{id}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/delete/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });


    // 4. SHARED FEATURES (Both Admin and Staff can open these routes)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{item}/history', [InventoryController::class, 'history'])->name('inventory.history');
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/borrow-release', [BorrowReleaseController::class, 'create'])->name('borrow-release.create');
    Route::post('/borrow-release', [BorrowReleaseController::class, 'store'])->name('borrow-release.store');
    Route::get('/delivery-monitoring', [
    BorrowReleaseController::class,
    'monitoring'
])->name('delivery-monitoring.index');
    Route::get('/returns', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');

    // System Utilities
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-json', [NotificationController::class, 'unreadJson'])->name('notifications.unread-json');
});

require __DIR__.'/auth.php';
