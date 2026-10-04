<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'registration_type',
        'password',
        'reset_token',
        'remember',
        'mobile_number',
        'photo',
        'user_location',
        'user_ip',
        'user_browser',
        'username',
        'verification_code',
        'email_verified_at',
        'is_verified',
        'user_country',
        'user_region',
        'user_city',
        'device_type',
        'device_name',
        'remember_token',
        'user_agent',
        'points',
        'available_points',
        'level_id',
        'balance',
        'available_balance',
        'bonus_balance',
        'vip_bonus',
        'cashback',
        'kyc_verified',
        'is_banned',
        'date_of_birth',
        'country',
        'currency',
        'referral_code',
        'referrer',
        'last_login_at',
        'google2fa_secret',
        'google2fa_status',
        'user_id',
        'ban_reason',

    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'kyc_verified' => 'boolean',
        'is_banned' => 'boolean',
        'date_of_birth' => 'date',
        'last_login_at' => 'datetime',
        'google2fa_status' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'kyc_verified' => 'boolean',
            'is_banned' => 'boolean',
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
            'google2fa_status' => 'boolean',
        ];
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    public function hasFavouriteGame($gameId)
    {
        return $this->favorites()->where('game_id', $gameId)->exists();
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function game_opens()
    {
        return $this->hasMany(GameOpen::class, 'user_id');
    }

    public function user_logins()
    {
        return $this->hasMany(UserLogin::class, 'user_id');
    }

    public function kycSubmissions()
    {
        return $this->hasMany(UserKycSubmission::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('is_read');
    }
    public function deposits()
    {
        return $this->hasMany(UserDeposit::class);
    }

    public function banDocuments()
    {
        return $this->hasMany(BanDocument::class);
    }

    public function vipBonuses()
    {
        return $this->hasMany(VipBonus::class);
    }



    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->user_id)) {
                $user->user_id = self::generateTimeBasedUserId();
            }
        });
    }

    private static function generateTimeBasedUserId()
    {

        $micro = (int) (microtime(true) * 1000000);
        $nano  = hrtime(true) % 1000;
        $sec   = date('s');
        $min   = date('i');

        // Combine and take last 10 digits
        $id = substr($micro . $nano . $sec . $min, -10);

        if (\App\Models\User::where('user_id', $id)->exists()) {
            $id .= rand(0, 9);
        }

        return $id;
    }
}
