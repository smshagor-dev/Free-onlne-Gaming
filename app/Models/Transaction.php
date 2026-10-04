<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = ['user_id', 'session_id', 'trx_type', 'trx', 'remark', 'casino_details', 'provider_name', 'provider_transaction_id', 'provider_action', 'user_deposit_id', 'user_withdrew_id', 'amount', 'status', 'comments', 'transaction_number', 'transaction_type', 'point_amount'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deposit()
    {
        return $this->belongsTo(UserDeposit::class, 'user_deposit_id');
    }

    public function withdraw()
    {
        return $this->belongsTo(UserWithdrew::class, 'user_withdrew_id');
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
