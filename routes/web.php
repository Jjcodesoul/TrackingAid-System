<?php


use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockController;

Route::get('/inventory', [InventoryController::class, 'index']);
Route::post('/inventory/store', [InventoryController::class, 'store']);

Route::get('/inventory/edit/{id}', [InventoryController::class, 'edit']);
Route::post('/inventory/update/{id}', [InventoryController::class, 'update']);
Route::post('/inventory/delete/{id}', [InventoryController::class, 'delete']);

Route::get('/stock-in', [StockController::class, 'create']);
Route::post('/stock-in/store', [StockController::class, 'store']);
