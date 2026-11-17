<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'amount', 'currency', 'asset', 'network',
        'destination', 'status', 'reviewed_by', 'reviewed_at',
        'rejection_reason', 'payout_txid', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'destination' => 'encrypted',
        'reviewed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected $hidden = ['destination'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function maskedDestination(): string
    {
        $address = (string) $this->destination;
        return strlen($address) > 12 ? substr($address, 0, 5).'…'.substr($address, -5) : 'Hidden';
    }
}
