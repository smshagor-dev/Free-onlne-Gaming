<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameOpen extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'points',
        'review',
        'comments',
        'like',
        'dislike',
        'bookmark',
        'report',
    ];

    protected $casts = [
        'bookmark' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function gamePoint()
    {
        return $this->belongsTo(GamePoint::class, 'game_id', 'game_id');
    }
}
