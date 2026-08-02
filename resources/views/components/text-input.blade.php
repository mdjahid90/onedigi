@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'input input-bordered w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-60']) !!}>
