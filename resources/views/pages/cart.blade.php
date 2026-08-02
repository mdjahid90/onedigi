<x-site-layout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between">
            <div class="text-sm font-semibold text-gray-900">{{ __('ui.cart') }}</div>
            <a href="{{ route('products.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __('ui.continue_shopping') }}</a>
        </div>

        @if(count($items ?? []) === 0)
            <div class="mt-6 bg-white border border-gray-100 rounded-lg p-6 text-sm text-gray-600">
                {{ __('ui.cart_empty') }}
            </div>
        @else
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-3">
                    @foreach($items as $item)
                        @php
                            $product = $item['product'];
                        @endphp
                        <div class="bg-white border border-gray-100 rounded-lg p-4">
                            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                <a href="{{ route('products.show', $product) }}" class="h-16 w-16 rounded-md bg-gray-100 shrink-0 overflow-hidden block">
                                    @if(!empty($product->image))
                                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->title }}" class="h-full w-full object-cover" />
                                    @endif
                                </a>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs text-gray-500">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                                    <a href="{{ route('products.show', $product) }}" class="text-sm font-semibold text-gray-900 hover:text-indigo-600">{{ $product->title }}</a>
                                    <div class="mt-1 text-sm text-gray-700"><x-money :amount="(float) ($item['unit_price'] ?? $product->price)" /></div>

                                    @php
                                        $meta = is_array($item['meta'] ?? null) ? ($item['meta'] ?? []) : [];
                                        $optionLabels = is_array($meta['option_labels'] ?? null) ? $meta['option_labels'] : [];
                                        $rawOptions = is_array($meta['options'] ?? null) ? $meta['options'] : [];
                                        $displayOptions = count($optionLabels) > 0 ? $optionLabels : $rawOptions;
                                        $displayOptions = is_array($displayOptions) ? $displayOptions : [];
                                        $fields = is_array($meta['fields'] ?? null) ? array_filter($meta['fields'], fn($v) => $v !== '' && $v !== null) : [];
                                    @endphp
                                    @if(!empty($displayOptions))
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($displayOptions as $label => $value)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-medium text-indigo-700 ring-1 ring-indigo-100">
                                                    {{ $label }}: {{ $value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(!empty($fields))
                                        <div class="mt-1 text-xs text-gray-500">
                                            @foreach($fields as $key => $value)
                                                <span class="mr-2"><span class="font-medium capitalize">{{ str_replace('_', ' ', $key) }}</span>: {{ mb_strlen($value) > 40 ? mb_substr($value, 0, 40).'…' : $value }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(($item['quantity'] ?? 1) > 1)
                                        <div class="mt-2 text-sm font-semibold text-gray-900 sm:hidden"><x-money :amount="(float) $item['subtotal']" /></div>
                                    @endif

                                    <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <form method="POST" action="{{ route('cart.update', $product) }}" class="flex items-center gap-2 w-full sm:w-auto" data-qty-form="1">
                                            @csrf
                                            <input type="hidden" name="cart_key" value="{{ $item['cart_key'] ?? $product->id }}">
                                            <button type="button" class="h-9 w-10 rounded-md border border-gray-200 text-sm font-semibold" data-qty-step="-1" aria-label="Decrease quantity">-</button>
                                            <input name="quantity" type="number" min="1" max="99" value="{{ $item['quantity'] }}" class="h-9 w-16 rounded-md border-gray-200 text-center" inputmode="numeric" />
                                            <button type="button" class="h-9 w-10 rounded-md border border-gray-200 text-sm font-semibold" data-qty-step="1" aria-label="Increase quantity">+</button>
                                        </form>

                                        <form method="POST" action="{{ route('cart.remove', $product) }}" class="w-full sm:w-auto">
                                            @csrf
                                            <input type="hidden" name="cart_key" value="{{ $item['cart_key'] ?? $product->id }}">
                                            <button class="h-9 w-full sm:w-auto px-3 rounded-md border border-gray-200 text-sm font-medium">{{ __('ui.remove') }}</button>
                                        </form>
                                    </div>
                                </div>

                                @if(($item['quantity'] ?? 1) > 1)
                                    <div class="hidden sm:block text-sm font-semibold text-gray-900"><x-money :amount="(float) $item['subtotal']" /></div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="bg-white border border-gray-100 rounded-lg p-4 h-fit">
                    <div class="text-sm font-semibold text-gray-900">{{ __('ui.summary') }}</div>
                    <div class="mt-3 flex items-center justify-between text-sm">
                        <span class="text-gray-600">{{ __('ui.total') }}</span>
                        <span class="font-semibold text-gray-900"><x-money :amount="(float) ($total ?? 0)" /></span>
                    </div>

                    <div class="mt-4 text-xs text-gray-600">
                        {{ __('ui.delivery_note') }}
                    </div>

                    <a href="{{ route('checkout') }}" class="mt-4 inline-flex w-full h-10 items-center justify-center rounded-md bg-indigo-600 text-white text-sm font-medium">{{ __('ui.proceed_to_checkout') }}</a>

                    <form method="POST" action="{{ route('cart.clear') }}" class="mt-3">
                        @csrf
                        <button class="w-full h-10 rounded-md border border-gray-200 text-sm font-medium">{{ __('ui.clear_cart') }}</button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('form[data-qty-form="1"]');
            const debounceMap = new Map();

            function clamp(v, min, max) {
                if (Number.isNaN(v)) return min;
                return Math.min(max, Math.max(min, v));
            }

            function debounce(key, fn, delay) {
                if (debounceMap.has(key)) {
                    clearTimeout(debounceMap.get(key));
                }
                const t = setTimeout(fn, delay);
                debounceMap.set(key, t);
            }

            forms.forEach(function (form) {
                const input = form.querySelector('input[name="quantity"]');
                const stepButtons = form.querySelectorAll('button[data-qty-step]');

                if (!input) return;

                stepButtons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const step = parseInt(btn.getAttribute('data-qty-step') || '0', 10);
                        const current = parseInt(input.value || '1', 10);
                        const next = clamp(current + step, 1, 99);
                        input.value = String(next);
                        form.submit();
                    });
                });

                input.addEventListener('input', function () {
                    debounce(input, function () {
                        const current = parseInt(input.value || '1', 10);
                        input.value = String(clamp(current, 1, 99));
                        form.submit();
                    }, 450);
                });
            });
        });
    </script>
</x-site-layout>
