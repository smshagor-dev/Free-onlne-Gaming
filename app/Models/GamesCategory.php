<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class GamesCategory extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'subtitle', 'name', 'slug', 'image', 'home_image'];

    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }


    public function getSlugAttribute($value)
    {
        return $value ?? Str::slug($this->name);
    }
}
