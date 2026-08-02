<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline rounded-lg']) }}>
    {{ $slot }}
</button>
