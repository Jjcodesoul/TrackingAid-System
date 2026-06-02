<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController; 

Route::get('/', function () {
    return view('welcome');
});

Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');