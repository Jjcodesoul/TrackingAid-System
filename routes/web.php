<?php

use App\Http\Controllers\BorrowReleaseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('requests.index');
});

Route::get('/inventory', [InventoryController::class, 'index']);
Route::post('/inventory/store', [InventoryController::class, 'store']);
Route::get('/inventory/edit/{id}', [InventoryController::class, 'edit']);
Route::post('/inventory/update/{id}', [InventoryController::class, 'update']);
Route::post('/inventory/delete/{id}', [InventoryController::class, 'delete']);

Route::get('/stock-in', [StockController::class, 'create']);
Route::post('/stock-in/store', [StockController::class, 'store']);

Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
Route::post('/requests/{request}/approve', [RequestController::class, 'approve'])->name('requests.approve');
Route::post('/requests/{request}/reject', [RequestController::class, 'reject'])->name('requests.reject');

Route::get('/borrow-release', [BorrowReleaseController::class, 'create'])->name('borrow-release.create');
Route::post('/borrow-release', [BorrowReleaseController::class, 'store'])->name('borrow-release.store');

Route::get('/returns', [ReturnController::class, 'create'])->name('returns.create');
Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
