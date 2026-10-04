<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'name',
        'title',
        'description',
        'seo_title',
        'seo_description',
        'meta_tag',
        'logo',
        'favicon',
        'thumbnail_image',
        'phone',
        'email',
        'adsense_code',
        'google_analytics_code',
        'MAIL_MAILER',
        'MAIL_HOST',
        'MAIL_PORT',
        'MAIL_USERNAME',
        'MAIL_PASSWORD',
        'MAIL_ENCRYPTION',
        'MAIL_FROM_ADDRESS',
        'MAIL_FROM_NAME',
        'site_currency',
        'currency_symble',
    ];
}
