<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BonusUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deposit_setting_id',
        'bonus_type',
        'user_deposit_id',
        'bonus_amount',
        'deposit_amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function depositSetting()
    {
        return $this->belongsTo(DepositSetting::class, 'deposit_setting_id', 'id');
    }

    public function userDeposit()
    {
        return $this->belongsTo(UserDeposit::class);
    }
}
