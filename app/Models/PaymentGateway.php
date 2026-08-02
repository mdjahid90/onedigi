<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentGateway extends Model
{
    protected $fillable = [
        'code',
        'name',
        'base_url',
        'api_key',
        'secret_key',
        'mode',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected function apiKey(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $this->decryptSecret($value),
            set: fn (?string $value): ?string => $this->encryptSecret($value),
        );
    }

    protected function secretKey(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $this->decryptSecret($value),
            set: fn (?string $value): ?string => $this->encryptSecret($value),
        );
    }

    private function decryptSecret(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $value;
        }
    }

    private function encryptSecret(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return Crypt::encryptString($value);
    }
}
