<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = ['points', 'level', 'achievements', 'photo'];

    public function points()
    {
        return $this->belongsTo(GamePoint::class, 'points_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
