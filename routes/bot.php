<?php

// Добавь в routes/web.php
use App\Telegram\Controllers\BotController;
use Illuminate\Support\Facades\Route;

Route::get('/bot/webapp', [BotController::class, 'webapp'])->name('bot.webapp');
Route::middleware('tma.auth')->group(function () {
  Route::get('/bot/webapp/cart', [BotController::class, 'cart'])->name('bot.webapp.cart');
});