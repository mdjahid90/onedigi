<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminNotification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'body',
        'url',
        'severity',
        'notifiable_type',
        'notifiable_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
