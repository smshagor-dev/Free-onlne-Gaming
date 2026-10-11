<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
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

        View::share('setting', $setting);
    }
}
