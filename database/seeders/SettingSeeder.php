<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'name' => 'Free Games',
            'title' => 'My Website Title',
            'description' => 'This is a sample description for the website settings.',
            'seo_title' => 'Best Website for Demo',
            'seo_description' => 'This is an example SEO description for testing purposes.',
            'meta_tag' => 'laravel,php,example,settings',
            'logo' => 'uploads/logo.png',
            'favicon' => 'uploads/favicon.ico',
            'thumbnail_image' => 'uploads/thumbnail.jpg',
            'phone' => '+1234567890',
            'email' => 'admin@example.com',
            'adsense_code' => '<script>/* Google Adsense Example */</script>',
            'google_analytics_code' => '<script>/* Google Analytics Example */</script>',
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.example.com',
            'MAIL_PORT' => '587',
            'MAIL_USERNAME' => 'user@example.com',
            'MAIL_PASSWORD' => 'securepassword',
            'MAIL_ENCRYPTION' => 'tls',
            'MAIL_FROM_ADDRESS' => 'no-reply@example.com',
            'MAIL_FROM_NAME' => 'Free Games',
        ]);
    }
}
