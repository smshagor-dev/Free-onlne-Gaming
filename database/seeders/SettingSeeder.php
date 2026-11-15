<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Never overwrite production site settings or SMTP credentials on re-seed.
        // SMTP is configured by an authorized admin or via server environment.
        Setting::firstOrCreate([], [
            'name' => 'Free Games',
            'title' => 'Free Games',
            'description' => 'Find and play free browser games.',
            'seo_title' => 'Free Games | Play Online',
            'seo_description' => 'Discover free browser games online.',
            'site_currency' => 'USD',
            'currency_symble' => '$',
        ]);
    }
}
