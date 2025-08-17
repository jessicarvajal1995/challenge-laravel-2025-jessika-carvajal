<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Orders API Routes
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']); // GET /api/orders
    Route::post('/', [OrderController::class, 'store']); // POST /api/orders
    Route::get('/{id}', [OrderController::class, 'show']); // GET /api/orders/{id}
    Route::post('/{id}/advance', [OrderController::class, 'advanceStatus']); // POST /api/orders/{id}/advance
});
