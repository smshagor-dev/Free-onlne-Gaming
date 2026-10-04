<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class CatalogSavedGame extends Model
{
    protected $fillable = ['user_id', 'provider', 'provider_game_id', 'title', 'image', 'genres', 'platforms'];

    protected function casts(): array
    {
        return ['genres' => 'array', 'platforms' => 'array'];
    }
}
