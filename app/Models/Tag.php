<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'tag_name', 'slug', 'photo', 'title', 'subtitle'];

    public function category()
    {
        return $this->belongsTo(GamesCategory::class, 'category_id');
    }

    public function games()
    {
        return $this->hasMany(Game::class, 'tag_id'); 
    }

    protected static function booted()
    {
        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }
}
