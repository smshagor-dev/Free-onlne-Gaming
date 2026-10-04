<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWithdrew extends Model
{
    protected $table = 'user_withdrew';

    public $timestamps = true;

    protected $fillable = [
        'transaction_number', 'user_id', 'gateway_id', 'amount', 'status', 'comments'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function gateway() {
        return $this->belongsTo(Gateway::class);
    }

    public function documents()
    {
        return $this->hasMany(UserWithdrawDocument::class, 'user_withdrew_id');
    }

    public function transaction() {
        return $this->hasOne(Transaction::class, 'user_withdrew_id');
    }
}
