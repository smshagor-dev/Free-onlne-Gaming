<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory;

    protected $table = 'countries';

    protected $fillable = [
        'name',
        'iso',
        'iso3',
        'dial',
        'currency',
        'currency_name',
        'currency_symbol',
        'status',
    ];
}
