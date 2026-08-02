@props([
    'amount' => 0,
])

@php
    $currency = app(\App\Services\CurrencyService::class);
    $value = is_numeric($amount) ? (float) $amount : 0.0;
@endphp

{{ $currency->format($value) }}
