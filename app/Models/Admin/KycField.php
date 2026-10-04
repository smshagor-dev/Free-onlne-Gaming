<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KycField extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'is_required',
        'input_type',
    ];
}
