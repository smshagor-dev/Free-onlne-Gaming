<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\UserLogin;
use App\Models\GameOpen;
use App\Models\Setting;
use App\Models\Page;
use App\Models\LastPlay;
use App\Models\GamesCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load global settings from DB
        try {
            $setting = Cache::remember('app.settings.first', now()->addMinutes(10), fn () => Setting::first());
        } catch (Throwable) {
            $setting = null;
        }

        $setting ??= (object) [
            'name' => config('app.name'),
            'title' => config('app.name'),
            'description' => '',
            'seo_title' => config('app.name'),
            'seo_description' => '',
            'meta_tag' => '',
            'logo' => null,
            'favicon' => null,
            'thumbnail_image' => null,
            'adsense_code' => null,
            'google_analytics_code' => null,
        ];

        if ($setting instanceof Setting) {
            // Override app name
            config(['app.name' => $setting->name ?? config('app.name')]);

            // Override mail configuration dynamically
            config([
                'mail.mailer' => $setting->MAIL_MAILER ?? config('mail.mailer'),
                'mail.host' => $setting->MAIL_HOST ?? config('mail.host'),
                'mail.port' => $setting->MAIL_PORT ?? config('mail.port'),
                'mail.username' => $setting->MAIL_USERNAME ?? config('mail.username'),
                'mail.password' => $setting->MAIL_PASSWORD ?? config('mail.password'),
                'mail.encryption' => $setting->MAIL_ENCRYPTION ?? config('mail.encryption'),
                'mail.from.address' => $setting->MAIL_FROM_ADDRESS ?? config('mail.from.address'),
                'mail.from.name' => $setting->MAIL_FROM_NAME ?? config('mail.from.name'),
            ]);
        }

        // Share variables with views
        View::composer(['layouts.app', 'layouts.admin'], function ($view) use ($setting) {
            $user = Auth::user();

            $lastLogin = $user
                ? UserLogin::where('user_id', $user->id)->latest('created_at')->first()
                : null;

            $gameCategories = Cache::remember('app.nav.game_categories', now()->addMinutes(10), fn () => GamesCategory::get());
            $pages = Cache::remember('app.nav.pages.active', now()->addMinutes(10), fn () => Page::where('status', 1)->get());
            $currentSlug = Request::segment(1);
            $gameOpenCounts = null;
            $casinoPlayCounts = null;

            if ($user) {
                $gameOpenCounts = GameOpen::query()
                    ->where('user_id', $user->id)
                    ->selectRaw('SUM(CASE WHEN game_id > 0 THEN 1 ELSE 0 END) as last_played_count')
                    ->selectRaw('SUM(CASE WHEN `like` = 1 THEN 1 ELSE 0 END) as favorites_count')
                    ->selectRaw('SUM(CASE WHEN bookmark = 1 THEN 1 ELSE 0 END) as bookmarks_count')
                    ->first();

                $casinoPlayCounts = LastPlay::query()
                    ->where('user_id', $user->id)
                    ->selectRaw('SUM(CASE WHEN last_play = 1 THEN 1 ELSE 0 END) as last_played_count')
                    ->selectRaw('SUM(CASE WHEN is_favourite = 1 THEN 1 ELSE 0 END) as favorites_count')
                    ->selectRaw('SUM(CASE WHEN is_bookmark = 1 THEN 1 ELSE 0 END) as bookmarks_count')
                    ->first();
            }

            $view->with([
                'user' => $user,
                'lastLogin' => $lastLogin,
                'setting' => $setting,
                'gameCategories' => $gameCategories,
                'lastPlayedCount' => (int) ($gameOpenCounts->last_played_count ?? 0),
                'favoritesCount'  => (int) ($gameOpenCounts->favorites_count ?? 0),
                'bookmarksCount'  => (int) ($gameOpenCounts->bookmarks_count ?? 0),
                'casinolastPlayedCount' => (int) ($casinoPlayCounts->last_played_count ?? 0),
                'casinofavoritesCount'  => (int) ($casinoPlayCounts->favorites_count ?? 0),
                'casinobookmarksCount'  => (int) ($casinoPlayCounts->bookmarks_count ?? 0),
                'pages' => $pages,
                'currentSlug' => $currentSlug,
            ]);
        });
    }
}
