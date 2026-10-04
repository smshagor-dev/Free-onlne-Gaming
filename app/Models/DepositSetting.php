<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DepositSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'bonus_type',
        'providers',
        'days',
        'wager',
        'bonus_percentage',
        'minimum_bonus',
        'bonus_time',
        'maximum_claim_in_a_day',
        'photo',
    ];

    protected $casts = [
        'providers' => 'array',
        'days' => 'array',
    ];

    public function gateways()
    {
        return $this->belongsToMany(Gateway::class, 'deposit_setting_gateway', 'deposit_setting_id', 'gateway_id');
    }
}
