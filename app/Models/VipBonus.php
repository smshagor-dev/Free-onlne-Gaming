<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VipBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bonus_amount',
        'playing_time',
        'wager',
    ];

    // Optional: relation to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    protected $casts = [
        'playing_time' => 'integer', 
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
