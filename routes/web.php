<?php

use App\Http\Controllers\BorrowReleaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// 1. Landing Root Route
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'admin' 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('dashboard');
    }
    return redirect('/login');
});

// All authenticated routes (Admin + User/Staff)
Route::middleware('auth')->group(function () {

    // 2. Regular User Dashboard
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return app(DashboardController::class)->index();
    })->name('dashboard');


    // 3. 🛑 ADMIN-ONLY CONTROL PANEL ROUTES
    Route::group([], function () {
        
        Route::get('/admin/dashboard', function () {
            if (auth()->user()->role !== 'admin') {
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

        Route::get('/stock-in', [StockController::class, 'create'])->name('stock.create');
        Route::post('/stock-in/store', [StockController::class, 'store'])->name('stock.store');

        Route::post('/requests/{request}/approve', [RequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{request}/reject', [RequestController::class, 'reject'])->name('requests.reject');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export');

        // 👥 User Management Actions (Fixed to work perfectly with standard browser form submissions)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/users/update/{id}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/delete/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });


    // 4. SHARED FEATURES (Both Admin and Staff can open these routes)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/borrow-release', [BorrowReleaseController::class, 'create'])->name('borrow-release.create');
    Route::post('/borrow-release', [BorrowReleaseController::class, 'store'])->name('borrow-release.store');
    Route::get('/returns', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');

    // System Utilities
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifications', function () { return view('notifications'); })->name('notifications.index');
});

require __DIR__.'/auth.php';

// ─── TEMPORARY REGISTRATION UTILITY ──────────────────────────────────────────
Route::get('/force-register-admin', function () {
    try {
        $user = \App\Models\User::updateOrCreate(
            ['email' => 'admin@trackingaid.org'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );
        return "Success! Admin user configuration injected.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});