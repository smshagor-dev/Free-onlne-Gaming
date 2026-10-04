<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    protected $fillable = [
        'category_id',
        'tag_id',
        'name',
        'games_url',
        'image',
        'details',
        'play_time',
        'slug',
    ];

    public function category()
    {
        return $this->belongsTo(GamesCategory::class, 'category_id');
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }

    public function gameOpens()
    {
        return $this->hasMany(GameOpen::class, 'game_id');
    }
}
