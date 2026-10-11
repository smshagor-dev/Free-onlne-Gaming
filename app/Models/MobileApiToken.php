<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileApiToken extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['token_hash', 'auth_fingerprint'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'last_used_at' => 'datetime'];
    }

    public static function fingerprint(User $user): string
    {
        return hash('sha256', $user->password.'|'.$user->google2fa_secret.'|'.(int) $user->google2fa_status);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
