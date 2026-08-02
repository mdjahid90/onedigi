<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'title_bn',
        'title_ru',
        'slug',
        'content',
        'content_bn',
        'content_ru',
        'is_published',
        'show_in_footer',
        'footer_order',
    ];

    protected $casts = [
        'is_published' => 'bool',
        'show_in_footer' => 'bool',
        'footer_order' => 'int',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
