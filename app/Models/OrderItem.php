<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'title',
        'unit_price',
        'quantity',
        'subtotal',
        'meta',
        'access_email',
        'access_password',
        'license_key',
        'subscription_starts_at',
        'subscription_expires_at',
        'entitlement_notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'meta' => 'array',
        'subscription_starts_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
