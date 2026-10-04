<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LottaryPrice extends Model
{
    use HasFactory;

    protected $table = 'lottaries_price';

    protected $fillable = [
        'lottary_id',
        'user_id',
        'name',
        'description',
        'price',
        'price_number',
    ];

    public function lottary()
    {
        return $this->belongsTo(Lottary::class, 'lottary_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prices()
    {
        return $this->hasMany(LottaryPrice::class, 'lottary_id', 'id');
    }
}
