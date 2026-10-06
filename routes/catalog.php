<?php

use App\Http\Controllers\CatalogLibraryController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\GameBrowseController;
use App\Http\Controllers\GamingPreferenceController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::post('/demo', [DemoController::class, 'login'])->middleware('guest')->name('demo.login');

Route::controller(GameBrowseController::class)->group(function (): void {
    Route::get('/games', 'index')->name('catalog.index');
    Route::get('/games/search', 'search')->name('catalog.search');
    Route::get('/games/free', 'free')->name('catalog.free');
    Route::get('/games/giveaways', 'giveaways')->name('catalog.giveaways');
    Route::get('/games/deals', 'deals')->name('catalog.deals');
    Route::get('/games/{provider}/{id}', 'show')
        ->where('provider', 'rawg|freetogame|gamerpower|cheapshark')
        ->where('id', '[^/]+')->name('catalog.show');
});

Route::middleware(['auth', 'ban'])->prefix('user/catalog')->name('catalog.user.')->group(function (): void {
    Route::get('/', [CatalogLibraryController::class, 'index'])->name('library');
    Route::post('/saved/{provider}/{id}', [CatalogLibraryController::class, 'store'])
        ->where('provider', 'rawg|freetogame|gamerpower|cheapshark')->name('saved.store');
    Route::delete('/saved/{provider}/{id}', [CatalogLibraryController::class, 'destroy'])
        ->where('provider', 'rawg|freetogame|gamerpower|cheapshark')->name('saved.destroy');
    Route::delete('/recent', [CatalogLibraryController::class, 'clearRecent'])->name('recent.clear');
    Route::get('/settings', [GamingPreferenceController::class, 'edit'])->name('settings');
    Route::put('/settings', [GamingPreferenceController::class, 'update'])->name('settings.update');
});
