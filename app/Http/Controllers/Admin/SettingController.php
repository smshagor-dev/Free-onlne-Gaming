<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Throwable;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::first() ?? Setting::create(['name' => 'Free Games']);
        $countries = Country::query()->select('id', 'currency_symbol', 'currency')
            ->whereNotNull('currency')->orderBy('currency')->get()->unique('currency');
        // Fallback while deployments migrate: the existing country list remains
        // available even before the new canonical currency table is installed.
        $currencies = Schema::hasTable('currencies')
            ? Currency::query()->orderBy('code')->get()
            : collect();

        return view('admin.settings.edit', compact('setting', 'countries', 'currencies'));
    }

    public function update(Request $request, Setting $setting): RedirectResponse
    {
        $data = $request->validate([
            'settings_tab' => ['sometimes', 'in:general,brand,seo,analytics,mail'],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'seo_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seo_description' => ['sometimes', 'nullable', 'string'],
            'meta_tag' => ['sometimes', 'nullable', 'string', 'max:255'],
            'logo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'favicon' => ['sometimes', 'nullable', 'file', 'mimes:ico,png', 'max:1024'],
            'thumbnail_image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'adsense_code' => ['sometimes', 'nullable', 'string'],
            'google_analytics_code' => ['sometimes', 'nullable', 'string'],
            'MAIL_MAILER' => ['sometimes', 'nullable', 'string', 'max:50'],
            'MAIL_HOST' => ['sometimes', 'nullable', 'string', 'max:255'],
            'MAIL_PORT' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:65535'],
            'MAIL_USERNAME' => ['sometimes', 'nullable', 'string', 'max:255'],
            'MAIL_PASSWORD' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'MAIL_ENCRYPTION' => ['sometimes', 'nullable', 'string', 'max:50'],
            'MAIL_FROM_ADDRESS' => ['sometimes', 'nullable', 'email', 'max:255'],
            'MAIL_FROM_NAME' => ['sometimes', 'nullable', 'string', 'max:255'],
            'site_currency' => ['sometimes', 'nullable', 'string', 'max:10'],
            'currency_symble' => ['sometimes', 'nullable', 'string', 'max:10'],
        ]);

        // The CMS displays multiple tabs in one form. Only modify the active
        // section, even if a browser submits stale/empty inputs from other tabs.
        // Keep compatibility with legacy requests that omit settings_tab.
        $tab = $data['settings_tab'] ?? null;
        unset($data['settings_tab']);
        if ($tab !== null) {
            $fields = match ($tab) {
                'general' => ['name', 'title', 'description', 'phone', 'email', 'site_currency', 'currency_symble'],
                'brand' => ['logo', 'favicon', 'thumbnail_image'],
                'seo' => ['seo_title', 'seo_description', 'meta_tag'],
                'analytics' => ['adsense_code', 'google_analytics_code'],
                'mail' => [
                    'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME',
                    'MAIL_PASSWORD', 'MAIL_ENCRYPTION', 'MAIL_FROM_ADDRESS',
                    'MAIL_FROM_NAME',
                ],
            };
            $data = array_intersect_key($data, array_flip($fields));
        }

        // These two legacy database columns are NOT NULL. Empty form inputs
        // must never overwrite the saved values with NULL.
        foreach (['site_currency', 'currency_symble'] as $field) {
            if (array_key_exists($field, $data) && blank($data[$field])) {
                unset($data[$field]);
            }
        }

        // Blank password is not a reset operation: never overwrite the SMTP secret.
        if (blank($data['MAIL_PASSWORD'] ?? null)) {
            unset($data['MAIL_PASSWORD']);
        }

        // Do not delete the previous media file during a normal settings update.
        // A bad upload or interrupted write must not destroy existing branding.
        foreach (['logo', 'favicon', 'thumbnail_image'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('settings', 'public');
            }
        }

        $setting->update($data);
        Cache::forget('app.settings.first');

        return back()->with('success', 'Site configuration saved successfully.');
    }

    public function testMail(Request $request): RedirectResponse
    {
        $admin = $request->user('admin');
        $key = 'admin:smtp-test:'.$admin->id.':'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 2)) {
            throw ValidationException::withMessages([
                'smtp' => 'Too many test emails. Please try again in a minute.',
            ]);
        }
        RateLimiter::hit($key, 60);

        if (config('mail.default') !== 'smtp') {
            return back()->with('error', 'Set the mail driver to SMTP and save it before sending a test.');
        }

        try {
            // Only the signed-in administrator receives test messages.
            // Never let arbitrary recipients use the server as an email relay.
            Mail::raw(
                'This is a Free Games CMS SMTP connectivity test. No action is required.',
                static function ($message) use ($admin): void {
                    $message->to($admin->email)->subject('Free Games SMTP test');
                }
            );
        } catch (Throwable) {
            return back()->with('error', 'SMTP test failed. Check host, port, encryption and credentials. No secret was changed.');
        }

        return back()->with('success', 'Test email handed to the configured SMTP mailer for '.$admin->email.'.');
    }
}
