<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class UserDeposit extends Model
{
    use HasFactory, Notifiable;

    public $timestamps = true;

    protected $fillable = ['user_id', 'gateway_id', 'amount', 'status', 'comments','transaction_number',];

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }

    public function documents()
    {
        return $this->hasMany(UserDepositDocument::class, 'user_deposit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
