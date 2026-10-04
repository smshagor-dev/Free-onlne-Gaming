<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamePoint extends Model
{
    protected $fillable = ['played_games', 'points','get_balance', 'points_amount'];

    public function gameOpens()
    {
        return $this->hasMany(GameOpen::class, 'game_id', 'game_id');
    }

    public static function getBalance($userId)
    {
        return self::where('user_id', $userId)->sum('points_amount');
    }
}
