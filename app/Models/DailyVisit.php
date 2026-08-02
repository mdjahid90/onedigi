<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyVisit extends Model
{
    protected $fillable = [
        'date',
        'visitors',
    ];

    protected $casts = [
        'date' => 'date',
        'visitors' => 'integer',
    ];
}
