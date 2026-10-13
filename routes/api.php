<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Middleware\ValidateReportApiKey;
use Illuminate\Support\Facades\Route;

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
    'updateStatus',
]);
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/dashboard', [DeliveryController::class, 'dashboard']);

    Route::get('/deliveries', [DeliveryController::class, 'index']);

    Route::get('/history', [DeliveryController::class, 'history']);

    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show']);

    Route::patch('/deliveries/{delivery}/status', [
        DeliveryController::class,
        'updateStatus',
    ]);
});

Route::middleware(ValidateReportApiKey::class)->group(function () {
    Route::get('/reports', [ReportController::class, 'apiIndex']);
    Route::post('/reports', [ReportController::class, 'apiStore']);
    Route::post('/reports/export', [ReportController::class, 'apiExport']);
    Route::post('/v1/external/reports/generate', [ReportController::class, 'generateExternalReport']);
});

Route::get('/v1/external/reports/{reportRequest}/download', [ReportController::class, 'downloadExternalReport'])
    ->middleware('signed')
    ->name('api.external-reports.download');
