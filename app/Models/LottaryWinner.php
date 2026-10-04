<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LottaryWinner extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'lottaries_winner';

    protected $fillable = [
        'lottary_id',
        'user_id',
        'transaction_id',
        'prize_id',
        'ticket_number',
        'price',
        'position',
    ];


    public function lottary()
    {
        return $this->belongsTo(Lottary::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(LotteryTransaction::class);
    }

    public function prize()
    {
        return $this->belongsTo(LottaryPrice::class, 'prize_id');
    }

    public function lotteryTransaction()
    {
        return $this->belongsTo(LotteryTransaction::class, 'transaction_id', 'id');
    }
}
