<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileGameState extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'favorite' => 'boolean', 'played_at' => 'datetime'];
    }
}
