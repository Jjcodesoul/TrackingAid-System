<?php

use App\Http\Controllers\BorrowReleaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $route = auth()->user()->role === 'admin' ? 'admin.dashboard' : 'dashboard';
        return redirect()->route($route);
    }
    return redirect('/login');
});

Route::middleware('auth')->group(function () {

    // Dashboard — auto redirect admin to admin.dashboard
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return app(DashboardController::class)->index();
    })->name('dashboard');

    // Admin Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'adminIndex'])->name('admin.dashboard');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/edit/{id}', [InventoryController::class, 'edit']);
    Route::post('/inventory/update/{id}', [InventoryController::class, 'update']);
    Route::post('/inventory/delete/{id}', [InventoryController::class, 'delete']);

    // Stock In
    Route::get('/stock-in', [StockController::class, 'create'])->name('stock.create');
    Route::post('/stock-in/store', [StockController::class, 'store'])->name('stock.store');

    // Requests
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::post('/requests/{request}/approve', [RequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{request}/reject', [RequestController::class, 'reject'])->name('requests.reject');

    // Borrow/Release
    Route::get('/borrow-release', [BorrowReleaseController::class, 'create'])->name('borrow-release.create');
    Route::post('/borrow-release', [BorrowReleaseController::class, 'store'])->name('borrow-release.store');

    // Returns
    Route::get('/returns', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/notifications', function () { return view('notifications');  })->name('notifications.index');
});

require __DIR__.'/auth.php';
