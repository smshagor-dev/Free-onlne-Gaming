<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LastPlay extends Model
{
    use HasFactory;

    protected $table = 'last_play';

    protected $fillable = [
        'user_id',
        'game_id',
        'last_play',
        'is_favourite',
        'is_bookmark',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
