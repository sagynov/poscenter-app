<?php

// Добавь в routes/web.php
use App\Telegram\Controllers\BotController;
use Illuminate\Support\Facades\Route;

Route::get('/bot/webapp', [BotController::class, 'webapp'])->name('bot.webapp');
Route::get('/bot/webapp/cart', [BotController::class, 'cart'])->name('bot.webapp.cart');
Route::get('/bot/webapp/checkout', [BotController::class, 'checkout'])->name('bot.webapp.checkout');