<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\UserLogin;
use App\Models\GameOpen;
use App\Models\Setting;
use App\Models\Page;
use App\Models\LastPlay;
use App\Models\GamesCategory;
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

            $gameCategories = GamesCategory::get();
            $pages = Page::where('status', 1)->get();
            $currentSlug = Request::segment(1);

            $view->with([
                'user' => $user,
                'lastLogin' => $lastLogin,
                'setting' => $setting,
                'gameCategories' => $gameCategories,
                'lastPlayedCount' => $user ? GameOpen::where('user_id', $user->id)->where('game_id', '>', 0)->count() : 0,
                'favoritesCount'  => $user ? GameOpen::where('user_id', $user->id)->where('like', 1)->count() : 0,
                'bookmarksCount'  => $user ? GameOpen::where('user_id', $user->id)->where('bookmark', 1)->count() : 0,
                'casinolastPlayedCount' => $user ? LastPlay::where('user_id', $user->id)->where('last_play', 1)->count() : 0,
                'casinofavoritesCount'  => $user ? LastPlay::where('user_id', $user->id)->where('is_favourite', 1)->count() : 0,
                'casinobookmarksCount'  => $user ? LastPlay::where('user_id', $user->id)->where('is_bookmark', 1)->count() : 0,
                'pages' => $pages,
                'currentSlug' => $currentSlug,
            ]);
        });
    }
}
