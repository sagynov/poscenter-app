<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/telegram/webhook', [App\Telegram\Controllers\BotController::class, 'index']);

Route::middleware('tma.auth')->group(function () {
    Route::get('/catalog', [CatalogController::class, 'index']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::apiResource('/cart', CartController::class);
    Route::apiResource('/orders', OrderController::class);
});