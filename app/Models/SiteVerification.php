<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVerification extends Model
{
    protected $fillable = [
        'google_search_console',
        'bing_webmaster',
        'yandex',
        'pinterest',
    ];
}
