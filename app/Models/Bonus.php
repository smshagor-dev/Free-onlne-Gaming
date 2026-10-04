<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'bonus_amount',
        'bonus_type',
        'photo',
        'description',
        'start_date',
        'end_date',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];
    
}
