<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashbackSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashback_percentage',
        'lose_calculation',
        'wager',
        'playing_time',
        'activation_days',
        'maximum_claim',
        'level_id',
    ];
    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}
