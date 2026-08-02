<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'ip_address',
        'user_agent',
        'referer',
        'url',
        'headers',
        'meta',
        'read_at',
    ];

    protected $casts = [
        'headers' => 'array',
        'meta' => 'array',
        'read_at' => 'datetime',
    ];
}
