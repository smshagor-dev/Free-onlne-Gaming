<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class CatalogRecentGame extends Model
{
    protected $fillable = ['user_id', 'provider', 'provider_game_id', 'title', 'image', 'genres', 'platforms', 'viewed_at'];

    protected function casts(): array
    {
        return ['genres' => 'array', 'platforms' => 'array', 'viewed_at' => 'datetime'];
    }
}
