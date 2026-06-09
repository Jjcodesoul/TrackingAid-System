<?php

use App\Http\Controllers\BorrowReleaseController;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;      
use Illuminate\Support\Facades\Route;

// Root route - redirect to dashboard if authenticated, login otherwise
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    // Using a clear literal string route redirect if standard login names conflict
    return redirect('/login');
});

// Protected Routes - Require Authentication
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory/store', [InventoryController::class, 'store']);
    Route::get('/inventory/edit/{id}', [InventoryController::class, 'edit']);
    Route::post('/inventory/update/{id}', [InventoryController::class, 'update']);
    Route::post('/inventory/delete/{id}', [InventoryController::class, 'delete']);

    // Stock In
    Route::get('/stock-in', [StockController::class, 'create']);
    Route::post('/stock-in/store', [StockController::class, 'store']);

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

    // Users & Roles Management (Added resource endpoints to handle user creation & actions)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// Authentication Routes
require __DIR__.'/auth.php';