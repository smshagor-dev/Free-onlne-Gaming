<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'image', 'currency', 'symbol', 'min_amount', 'max_amount', 'instruction','withdraw_instruction', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function requiredDocuments()
    {
        return $this->hasMany(RequiredDocument::class);
    }
}
