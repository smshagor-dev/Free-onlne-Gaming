<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CasinoController;
use App\Http\Controllers\CasinoBonusController;
use App\Http\Controllers\CasinoCashbackController;
use App\Http\Controllers\CasinoVipBonusController;
use App\Http\Controllers\GameCatalogController;


// Route::match(['get','post'], '/casino/player', [CasinoController::class, 'casinoPlayer']);
// Route::match(['get','post'], '/casino-bonus/player', [CasinoBonusController::class, 'casinoPlayer']);

Route::any('/casino/player', [CasinoController::class, 'casinoPlayer']);
Route::any('/casino-bonus/player', [CasinoBonusController::class, 'casinoPlayer']);
Route::any('/casino-cashback/player', [CasinoCashbackController::class, 'casinoPlayer']);
Route::any('/vip/casino-bonus/player', [CasinoVipBonusController::class, 'casinoPlayer']);

Route::prefix('games')->controller(GameCatalogController::class)->group(function (): void {
    Route::get('/discover', 'discover');
    Route::get('/search', 'search');
    Route::get('/free-to-play', 'freeToPlay');
    Route::get('/giveaways', 'giveaways');
    Route::get('/deals', 'deals');
    Route::get('/genres', 'genres');
    Route::get('/platforms', 'platforms');
    Route::get('/{provider}/{id}', 'details')
        ->where('provider', 'rawg|freetogame|gamerpower|cheapshark');
});
