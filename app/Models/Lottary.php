<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lottary extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'photo',
        'price',
        'prize_number',
        'winner_number',
        'draw_date',
        'is_draw',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'draw_date' => 'date',
        'is_draw' => 'boolean',
    ];

    public function prizes()
    {
        return $this->hasMany(LottaryPrice::class, 'lottary_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(LotteryTransaction::class, 'lottary_id', 'id');
    }

    public function prices()
    {
        return $this->hasMany(LottaryPrice::class, 'lottary_id', 'id');
    }
}
