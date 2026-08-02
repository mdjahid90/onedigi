<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'event_type',
        'occurred_at',
        'user_id',
        'subject_type',
        'subject_id',
        'route_name',
        'path',
        'referrer',
        'session_hash',
        'ip_address',
        'country_code',
        'country_name',
        'device_type',
        'browser',
        'source',
        'meta',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'meta' => 'array',
    ];
}
