<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequiredDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway_id', 'name', 'type','name_withdraw',
    ];

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }
}
