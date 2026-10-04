<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserKycSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kyc_field_id',
        'value',
        'status',
        'comments',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kycField()
    {
        return $this->belongsTo(\App\Models\Admin\KycField::class, 'kyc_field_id');
    }



}
