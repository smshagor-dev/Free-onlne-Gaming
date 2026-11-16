<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteCmsConfig extends Model
{
    protected $fillable = ['section', 'payload'];

    protected $casts = ['payload' => 'array'];
}
