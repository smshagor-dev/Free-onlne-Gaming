<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Country;

class SettingController extends Controller
{
    public function edit(Setting $setting)
    {
        $setting = Setting::first();

        $countries = Country::select('id', 'currency_symbol', 'currency')->get();

        return view('admin.settings.edit', compact('setting', 'countries'));
    }

    public function update(Request $request, Setting $setting)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'meta_tag' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png|max:1024',
            'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'adsense_code' => 'nullable|string',
            'google_analytics_code' => 'nullable|string',
            'MAIL_MAILER' => 'nullable|string|max:50',
            'MAIL_HOST' => 'nullable|string|max:255',
            'MAIL_PORT' => 'nullable|string|max:10',
            'MAIL_USERNAME' => 'nullable|string|max:255',
            'MAIL_PASSWORD' => 'nullable|string|max:255',
            'MAIL_ENCRYPTION' => 'nullable|string|max:50',
            'MAIL_FROM_ADDRESS' => 'nullable|email|max:255',
            'MAIL_FROM_NAME' => 'nullable|string|max:255',
            'site_currency' => 'nullable|string|max:10',
            'currency_symble' => 'nullable|string|max:10',
        ]);

        // Handle file uploads
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($setting->logo && Storage::exists($setting->logo)) {
                Storage::delete($setting->logo);
            }
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            // Delete old favicon if exists
            if ($setting->favicon && Storage::exists($setting->favicon)) {
                Storage::delete($setting->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        if ($request->hasFile('thumbnail_image')) {
            // Delete old thumbnail if exists
            if ($setting->thumbnail_image && Storage::exists($setting->thumbnail_image)) {
                Storage::delete($setting->thumbnail_image);
            }
            $data['thumbnail_image'] = $request->file('thumbnail_image')->store('settings', 'public');
        }

        $setting->update($data);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
