<?php

use App\Http\Controllers\Api\MobileAccountController as Account;
use App\Http\Controllers\Api\MobileAuthController as Auth;
use App\Http\Controllers\Api\MobileGameController as Games;
use App\Http\Middleware\MobileTokenAuth;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:mobile-general')->group(function () {
    Route::get('/config', fn () => response()->json(['data' => ['api_version' => 'v1', 'page_size' => 30, 'providers' => ['gamemonetize', 'local', 'freetogame', 'gamerpower'], 'features' => ['free_games' => true, 'casino' => false, 'payments' => false, 'lottery_read' => true, 'lottery_purchase' => false], 'token_lifetime_days' => 30]]));
    Route::prefix('auth')->middleware('throttle:mobile-auth')->group(function () {
        Route::post('/register', [Auth::class, 'register']);
        Route::post('/login', [Auth::class, 'login']);
        Route::post('/verify-email', [Auth::class, 'verify']);
        Route::post('/resend-verification', [Auth::class, 'resend'])->middleware('throttle:mobile-mail');
        Route::post('/forgot-password', [Auth::class, 'forgot'])->middleware('throttle:mobile-mail');
        Route::post('/reset-password', [Auth::class, 'reset']);
    });
    Route::get('/games', [Games::class, 'index']);
    Route::get('/categories', [Games::class, 'categories']);
    Route::get('/games/{provider}/{id}', [Games::class, 'show'])->where('provider', 'gamemonetize|local|freetogame|gamerpower')->where('id', '[A-Za-z0-9_-]{1,120}');
    Route::middleware(MobileTokenAuth::class)->group(function () {
        Route::get('/me', [Auth::class, 'me']);
        Route::patch('/me', [Auth::class, 'update']);
        Route::put('/me/password', [Auth::class, 'password'])->middleware('throttle:mobile-sensitive');
        Route::post('/auth/logout', [Auth::class, 'logout']);
        Route::post('/auth/logout-all', [Auth::class, 'logoutAll']);
        Route::post('/auth/refresh', [Auth::class, 'refresh'])->middleware('throttle:mobile-sensitive');
        Route::post('/me/2fa/setup', [Auth::class, 'setupTwoFactor'])->middleware('throttle:mobile-sensitive');
        Route::post('/me/2fa/enable', [Auth::class, 'enableTwoFactor'])->middleware('throttle:mobile-sensitive');
        Route::post('/me/2fa/disable', [Auth::class, 'disableTwoFactor'])->middleware('throttle:mobile-sensitive');
        Route::delete('/me', [Auth::class, 'deleteAccount'])->middleware('throttle:mobile-sensitive');
        Route::get('/me/devices', [Auth::class, 'devices']);
        Route::delete('/me/devices/{id}', [Auth::class, 'revoke'])->whereNumber('id');
        Route::get('/me/dashboard', [Account::class, 'dashboard']);
        Route::get('/me/preferences', [Account::class, 'preferences']);
        Route::put('/me/preferences', [Account::class, 'savePreferences']);
        Route::get('/me/favorites', [Games::class, 'favorites']);
        Route::get('/me/recent', [Games::class, 'recent']);
        Route::delete('/me/recent', [Games::class, 'clearRecent']);
        Route::put('/me/games/{provider}/{id}/favorite', [Games::class, 'favorite'])->where('provider', 'gamemonetize|local|freetogame|gamerpower')->where('id', '[A-Za-z0-9_-]{1,120}');
        Route::delete('/me/games/{provider}/{id}/favorite', [Games::class, 'unfavorite'])->where('provider', 'gamemonetize|local|freetogame|gamerpower')->where('id', '[A-Za-z0-9_-]{1,120}');
        Route::post('/me/games/{provider}/{id}/played', [Games::class, 'played'])->where('provider', 'gamemonetize')->where('id', '[A-Za-z0-9_-]{1,120}');
        Route::get('/me/notifications', [Account::class, 'notifications']);
        Route::patch('/me/notifications/read-all', [Account::class, 'readAll']);
        Route::patch('/me/notifications/{id}/read', [Account::class, 'read'])->whereNumber('id');
        Route::get('/lotteries', [Account::class, 'lotteries']);
        Route::get('/me/lottery/tickets', [Account::class, 'tickets']);
        Route::get('/me/lottery/tickets/{id}', [Account::class, 'ticket'])->whereNumber('id');
        Route::get('/me/lottery/wins', [Account::class, 'wins']);
    });
});
