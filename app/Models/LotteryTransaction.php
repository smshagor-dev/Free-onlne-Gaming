<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LotteryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lottary_id',
        'ticket_number',
        'transaction_number',
        'amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lottary()
    {
        return $this->belongsTo(Lottary::class, 'lottary_id');
    }

    public function winner()
    {
        return $this->hasOne(LottaryWinner::class, 'transaction_id', 'id');
    }
}
