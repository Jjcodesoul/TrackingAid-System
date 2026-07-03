<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 👇 THIS IS THE OFFICIAL DOORWAY FOR NIÑA'S WEBSITE 👇
Route::post('/incoming-requests', [RequestController::class, 'storeResqData']);