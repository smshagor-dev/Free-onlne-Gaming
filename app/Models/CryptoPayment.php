<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoPayment extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'provider', 'invoice_id', 'provider_payment_id', 'checkout_url',
        'amount', 'currency', 'status', 'additional_status',
        'settled_at', 'last_checked_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'settled_at' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
