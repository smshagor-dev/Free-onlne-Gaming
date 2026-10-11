<?php

use App\Http\Controllers\GameCatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('games')->controller(GameCatalogController::class)->group(function (): void {
    Route::get('/discover', 'discover');
    Route::get('/search', 'search');
    Route::get('/free-to-play', 'freeToPlay');
    Route::get('/giveaways', 'giveaways');
    Route::get('/genres', 'genres');
    Route::get('/platforms', 'platforms');
    Route::get('/{provider}/{id}', 'details')
        ->where('provider', 'freetogame|gamerpower');
});

require __DIR__.'/mobile.php';
