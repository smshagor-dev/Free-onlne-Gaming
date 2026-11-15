<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    // Never expose the SMTP credential through array/JSON serialization.
    protected $hidden = ['MAIL_PASSWORD'];

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

    /**
     * Decrypt new values while remaining compatible with legacy plaintext rows.
     * Existing credentials are not rewritten unless an admin replaces them.
     */
    public function getMailPasswordAttribute(?string $value): ?string
    {
        return $value !== null && str_starts_with($value, 'encrypted:v1:')
            ? Crypt::decryptString(substr($value, strlen('encrypted:v1:')))
            : $value;
    }

    public function setMailPasswordAttribute(?string $value): void
    {
        $this->attributes['MAIL_PASSWORD'] = filled($value)
            ? 'encrypted:v1:'.Crypt::encryptString($value)
            : null;
    }
}

