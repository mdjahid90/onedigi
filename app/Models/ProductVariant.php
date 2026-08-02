<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'price',
        'regular_price',
        'stock',
        'is_active',
        'options',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'regular_price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'options' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
