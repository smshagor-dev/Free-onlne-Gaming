<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CasinoController;
use App\Http\Controllers\CasinoBonusController;
use App\Http\Controllers\CasinoCashbackController;
use App\Http\Controllers\CasinoVipBonusController;


// Route::match(['get','post'], '/casino/player', [CasinoController::class, 'casinoPlayer']);
// Route::match(['get','post'], '/casino-bonus/player', [CasinoBonusController::class, 'casinoPlayer']);

Route::any('/casino/player', [CasinoController::class, 'casinoPlayer']);
Route::any('/casino-bonus/player', [CasinoBonusController::class, 'casinoPlayer']);
Route::any('/casino-cashback/player', [CasinoCashbackController::class, 'casinoPlayer']);
Route::any('/vip/casino-bonus/player', [CasinoVipBonusController::class, 'casinoPlayer']);

