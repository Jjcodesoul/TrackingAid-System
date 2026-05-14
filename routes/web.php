<?php

use App\Http\Controllers\BorrowReleaseController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReturnController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('requests.index');
});

Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
Route::post('/requests/{request}/approve', [RequestController::class, 'approve'])->name('requests.approve');
Route::post('/requests/{request}/reject', [RequestController::class, 'reject'])->name('requests.reject');

Route::get('/borrow-release', [BorrowReleaseController::class, 'create'])->name('borrow-release.create');
Route::post('/borrow-release', [BorrowReleaseController::class, 'store'])->name('borrow-release.store');

Route::get('/returns', [ReturnController::class, 'create'])->name('returns.create');
Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
