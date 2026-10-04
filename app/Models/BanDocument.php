<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BanDocument extends Model
{
    use HasFactory;

    protected $table = 'ban_documents';

    protected $fillable = [
        'user_id',
        'name',
        'submit_documents', // you may leave this empty if not uploading
        'status',
        'comments',
        'why_unban',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
