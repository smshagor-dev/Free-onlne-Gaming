<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CasinoGameSession extends Model
{
    use HasFactory;

    protected $table = 'casino_game_sessions';

    protected $fillable = [
        'user_id',
        'session_id',
        'game_name',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
