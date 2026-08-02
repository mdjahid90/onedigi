@props([
    'price' => null,
    'oldPrice' => null,
    'currency' => '৳',
])

@php
    $p = is_numeric($price) ? (float) $price : null;
    $op = is_numeric($oldPrice) ? (float) $oldPrice : null;
    $showOld = $op !== null && $p !== null && $op > $p;
@endphp

<div class="flex items-center gap-2">

    @if($showOld)
        <span class="text-red-400 line-through text-lg">
            <x-money :amount="$op" />
        </span>
    @endif

    @if($p !== null)
        <span class="text-red-600 font-bold text-2xl">
            <x-money :amount="$p" />
        </span>
    @endif

</div>