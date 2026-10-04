<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDepositDocument extends Model
{
    use HasFactory;

    protected $fillable = ['user_deposit_id', 'required_document_id', 'value'];

    public function userDeposit()
    {
        return $this->belongsTo(UserDeposit::class);
    }

    public function requiredDocument()
    {
        return $this->belongsTo(RequiredDocument::class, 'required_document_id');
    }
}
