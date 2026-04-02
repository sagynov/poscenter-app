<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/telegram/webhook', [App\Telegram\Controllers\BotController::class, 'index']);

Route::middleware('tma.auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::get('/catalog', [CatalogController::class, 'index']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::apiResource('/cart', CartController::class);
    Route::apiResource('/orders', OrderController::class);
});

Route::get('/import', ImportController::class);