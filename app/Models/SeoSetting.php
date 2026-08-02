<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    protected $fillable = [
        'global_title',
        'meta_description',
        'meta_keywords',
        'author_name',
        'og_image',
    ];
}
