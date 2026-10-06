<?php

namespace App\Providers;

use App\Models\GameOpen;
use App\Models\GamesCategory;
use App\Models\LastPlay;
use App\Models\Page;
use App\Models\Setting;
use App\Models\UserLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production') && config('security.force_https')) {
            URL::forceScheme('https');
        }

        try {
            $setting = Setting::first();
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
            config(['app.name' => $setting->name ?? config('app.name')]);

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

        View::composer(['layouts.app', 'layouts.admin'], function ($view) use ($setting): void {
            $user = Auth::user();

            $lastLogin = $user
                ? UserLogin::where('user_id', $user->id)->latest('created_at')->first()
                : null;

            try {
                $gameCategories = Cache::remember('layout.game_categories', 300, fn () => GamesCategory::get());
                $pages = Cache::remember('layout.active_pages', 300, fn () => Page::where('status', 1)->get());
            } catch (Throwable) {
                $gameCategories = GamesCategory::get();
                $pages = Page::where('status', 1)->get();
            }

            $gameStats = null;
            $casinoStats = null;

            if ($user) {
                $gameStats = GameOpen::query()
                    ->where('user_id', $user->id)
                    ->selectRaw('COUNT(CASE WHEN game_id > 0 THEN 1 END) AS last_played_count')
                    ->selectRaw('COUNT(CASE WHEN `like` = 1 THEN 1 END) AS favorites_count')
                    ->selectRaw('COUNT(CASE WHEN bookmark = 1 THEN 1 END) AS bookmarks_count')
                    ->first();

                $casinoStats = LastPlay::query()
                    ->where('user_id', $user->id)
                    ->selectRaw('COUNT(CASE WHEN last_play = 1 THEN 1 END) AS last_played_count')
                    ->selectRaw('COUNT(CASE WHEN is_favourite = 1 THEN 1 END) AS favorites_count')
                    ->selectRaw('COUNT(CASE WHEN is_bookmark = 1 THEN 1 END) AS bookmarks_count')
                    ->first();
            }

            $view->with([
                'user' => $user,
                'lastLogin' => $lastLogin,
                'setting' => $setting,
                'gameCategories' => $gameCategories,
                'lastPlayedCount' => (int) ($gameStats?->last_played_count ?? 0),
                'favoritesCount' => (int) ($gameStats?->favorites_count ?? 0),
                'bookmarksCount' => (int) ($gameStats?->bookmarks_count ?? 0),
                'casinolastPlayedCount' => (int) ($casinoStats?->last_played_count ?? 0),
                'casinofavoritesCount' => (int) ($casinoStats?->favorites_count ?? 0),
                'casinobookmarksCount' => (int) ($casinoStats?->bookmarks_count ?? 0),
                'pages' => $pages,
                'currentSlug' => Request::segment(1),
            ]);
        });
    }
}
