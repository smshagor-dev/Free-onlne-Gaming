<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class UserGamePreference extends Model
{
    protected $fillable = ['user_id', 'preferred_genres', 'preferred_platforms'];

    protected function casts(): array
    {
        return ['preferred_genres' => 'array', 'preferred_platforms' => 'array'];
    }
}
