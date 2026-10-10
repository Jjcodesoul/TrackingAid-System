<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeliveryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/incoming-requests', [RequestController::class, 'storeResqData']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

Route::get('/dashboard', [DeliveryController::class, 'dashboard']);
Route::get('/deliveries', [DeliveryController::class, 'index']);
Route::get('/history', [DeliveryController::class, 'history']);
Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show']);

Route::patch('/deliveries/{delivery}/status', [
    DeliveryController::class,
    'updateStatus'
]);Route::middleware('auth:sanctum')->group(function () {

    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/dashboard', [DeliveryController::class, 'dashboard']);

    Route::get('/deliveries', [DeliveryController::class, 'index']);

    Route::get('/history', [DeliveryController::class, 'history']);

    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show']);

    Route::patch('/deliveries/{delivery}/status', [
        DeliveryController::class,
        'updateStatus'
    ]);
});

Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'apiIndex']);
Route::post('/reports', [\App\Http\Controllers\ReportController::class, 'apiStore']);
Route::post('/reports/export', [\App\Http\Controllers\ReportController::class, 'apiExport']);