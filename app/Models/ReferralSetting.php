<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReferralSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'register_user',
        'total_deposit',
        'commission',
        'level',
    ];
}
