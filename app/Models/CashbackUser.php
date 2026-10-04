<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashbackUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'finished_time',
        'wager',
        'playing_time',
    ];

    protected $casts = [
        'finished_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
