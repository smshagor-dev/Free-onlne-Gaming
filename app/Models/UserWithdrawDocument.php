<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserWithdrawDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_withdrew_id',
        'required_document_id',
        'value',
    ];

    public function withdraw()
    {
        return $this->belongsTo(UserWithdrew::class, 'user_withdrew_id');
    }

    public function requiredDocument()
    {
        return $this->belongsTo(RequiredDocument::class, 'required_document_id');
    }
}
