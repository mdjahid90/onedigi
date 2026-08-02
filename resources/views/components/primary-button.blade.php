<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary rounded-lg']) }}>
    {{ $slot }}
</button>
