<?php

<<<<<<< HEAD
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // User Management System
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
=======
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
>>>>>>> db5ef8e73ac4431ebbfc800ae78adb114a103e05
