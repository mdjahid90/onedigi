<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-error rounded-lg']) }}>
    {{ $slot }}
</button>
