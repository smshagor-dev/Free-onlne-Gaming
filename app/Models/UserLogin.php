<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLogin extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'ip_address',
        'country',
        'region',
        'city',
        'browser',
        'device_type',
        'device_name',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
