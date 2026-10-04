<?php

use App\Http\Controllers\GameBrowseController;
use Illuminate\Support\Facades\Route;

Route::controller(GameBrowseController::class)->group(function (): void {
    Route::get('/games', 'index')->name('catalog.index');
    Route::get('/games/search', 'search')->name('catalog.search');
    Route::get('/games/free', 'free')->name('catalog.free');
    Route::get('/games/giveaways', 'giveaways')->name('catalog.giveaways');
    Route::get('/games/deals', 'deals')->name('catalog.deals');

    Route::get('/games/{provider}/{id}', 'show')
        ->where('provider', 'rawg|freetogame|gamerpower|cheapshark')
        ->where('id', '[^/]+')
        ->name('catalog.show');
});
