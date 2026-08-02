<x-site-layout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="mt-6 grid grid-cols-1 md:grid-cols-12 gap-6">
            <div class="md:hidden">
                <details class="rounded-2xl bg-white ring-1 ring-black/5 shadow-sm overflow-hidden">
                    <summary class="cursor-pointer list-none select-none px-4 py-3 flex items-center justify-between">
                        <div class="text-sm font-semibold text-gray-900">{{ __('ui.filters') }}</div>
                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="px-4 pb-4">
                        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 gap-3" data-auto-submit="products">
                            <input type="hidden" name="q" value="{{ request('q') }}" />
                            <input type="hidden" name="sort" value="{{ request('sort') }}" />
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">{{ __('ui.category') }}</label>
                                <select name="category" class="mt-1 w-full h-10 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <option value="" {{ request('category') === null || request('category') === '' ? 'selected' : '' }}>{{ __('ui.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700">{{ __('ui.min') }}</label>
                                    <input name="min_price" value="{{ request('min_price') }}" inputmode="decimal" class="mt-1 w-full h-10 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" placeholder="{{ __('ui.min') }}" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700">{{ __('ui.max') }}</label>
                                    <input name="max_price" value="{{ request('max_price') }}" inputmode="decimal" class="mt-1 w-full h-10 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" placeholder="{{ __('ui.max') }}" />
                                </div>
                            </div>

                            <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center h-10 px-4 rounded-xl bg-white border border-gray-200 text-sm font-semibold text-gray-900 hover:bg-gray-50 hover:border-gray-300 transition">{{ __('ui.reset') }}</a>
                        </form>
                    </div>
                </details>
            </div>

            <aside class="hidden md:block md:col-span-3">
                <div class="rounded-2xl bg-white ring-1 ring-black/5 shadow-sm p-4 sticky top-20">
                    <div class="text-sm font-semibold text-gray-900">{{ __('ui.filters') }}</div>
                    <div class="mt-1 text-xs text-gray-600">{{ $products->total() }} {{ __('ui.products_found') }}</div>

                    <form method="GET" action="{{ route('products.index') }}" class="mt-4 grid grid-cols-1 gap-3" data-auto-submit="products">
                        <input type="hidden" name="q" value="{{ request('q') }}" />
                        <input type="hidden" name="sort" value="{{ request('sort') }}" />
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">{{ __('ui.category') }}</label>
                            <select name="category" class="mt-1 w-full h-10 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <option value="" {{ request('category') === null || request('category') === '' ? 'selected' : '' }}>{{ __('ui.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">{{ __('ui.price_range') }}</label>
                            <div class="mt-1 grid grid-cols-2 gap-2">
                                <input name="min_price" value="{{ request('min_price') }}" inputmode="decimal" class="w-full h-10 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" placeholder="{{ __('ui.min') }}" />
                                <input name="max_price" value="{{ request('max_price') }}" inputmode="decimal" class="w-full h-10 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" placeholder="{{ __('ui.max') }}" />
                            </div>
                        </div>

                        <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center h-10 px-4 rounded-xl bg-white border border-gray-200 text-sm font-semibold text-gray-900 hover:bg-gray-50 hover:border-gray-300 transition">{{ __('ui.reset') }}</a>
                    </form>
                </div>
            </aside>

            <div class="md:col-span-9">
                <form method="GET" action="{{ route('products.index') }}" class="rounded-2xl bg-white ring-1 ring-black/5 shadow-sm p-3 sm:p-4" data-auto-submit="products">
                    <input type="hidden" name="category" value="{{ request('category') }}" />
                    <input type="hidden" name="min_price" value="{{ request('min_price') }}" />
                    <input type="hidden" name="max_price" value="{{ request('max_price') }}" />
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 21l-4.3-4.3m1.8-5.2a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input name="q" value="{{ request('q') }}" class="w-full h-10 rounded-xl border border-gray-200 bg-white pl-10 pr-3 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" placeholder="{{ __('ui.search_products') }}" />
                        </div>

                        <div class="sm:w-56">
                            <select name="sort" class="w-full h-10 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <option value="" {{ request('sort') === null || request('sort') === '' ? 'selected' : '' }}>{{ __('ui.sort_newest') }}</option>
                                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>{{ __('ui.sort_price_low_high') }}</option>
                                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ __('ui.sort_price_high_low') }}</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('products.index', request()->except(['q', 'page'])) }}" class="inline-flex items-center justify-center h-10 px-4 rounded-xl bg-white border border-gray-200 text-sm font-semibold text-gray-900 hover:bg-gray-50 hover:border-gray-300 transition">{{ __('ui.clear') }}</a>
                        </div>
                    </div>
                </form>

                <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                    @forelse($products as $product)
                        <div class="group bg-white rounded-2xl overflow-hidden ring-1 ring-black/5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition">
                            <a href="{{ route('products.show', $product) }}" class="block bg-gradient-to-b from-gray-50 to-white p-2" aria-label="View {{ $product->title }}">
                                @if(!empty($product->image))
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->title }}" class="aspect-square w-full rounded-xl object-cover bg-gray-100 transition-transform duration-300 group-hover:scale-[1.02]" width="320" height="320" loading="lazy" decoding="async" />
                                @else
                                    <div class="aspect-square rounded-xl bg-gray-100"></div>
                                @endif
                            </a>

                            <div class="p-3 sm:p-4 flex flex-col">
                                <div class="inline-flex">
                                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-[11px] font-semibold text-gray-600 ring-1 ring-gray-100">
                                        {{ $product->category?->name ?? 'Uncategorized' }}
                                    </span>
                                </div>
                                <a href="{{ route('products.show', $product) }}" class="mt-2 block text-sm sm:text-[15px] font-semibold leading-snug text-gray-900 hover:text-indigo-600">
                                    <span class="block h-[2.6em] overflow-hidden">{{ $product->title }}</span>
                                </a>
                                @if(($reviewsEnabled ?? false) && (int) ($product->reviews_count ?? 0) > 0)
                                    <div class="mt-1 inline-flex items-center gap-1 text-xs text-amber-600">
                                        <span>&#9733;</span>
                                        <span class="font-semibold">{{ number_format((float) $product->reviews_avg_rating, 1) }}</span>
                                        <span class="text-gray-500">({{ (int) $product->reviews_count }})</span>
                                    </div>
                                @endif

                                @php
                                    $minV = $product->min_variant_price;
                                    $maxV = $product->max_variant_price;
                                    $minP = $minV !== null ? (float) $minV : (float) $product->price;
                                    $maxP = $maxV !== null ? (float) $maxV : (float) $product->price;
                                    $regP = $product->regular_price ? (float) $product->regular_price : null;
                                    $isRange = $minP !== $maxP;
                                    $cardOutOfStock = $product->stock !== null && $product->stock <= 0;
                                @endphp

                                <div class="mt-2 flex items-center justify-between gap-2">
                                    <div>
                                        @if($regP && $regP > $minP && !$isRange)
                                            <div class="text-xs text-gray-400 line-through leading-tight"><x-money :amount="$regP" /></div>
                                        @endif
                                        <div class="text-sm font-semibold {{ $cardOutOfStock ? 'text-gray-400' : 'text-red-600' }}">
                                            @if($isRange)
                                                <x-money :amount="$minP" /> – <x-money :amount="$maxP" />
                                            @else
                                                <x-money :amount="$minP" />
                                            @endif
                                        </div>
                                    </div>
                                    @if($cardOutOfStock)
                                        <span class="shrink-0 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-500 ring-1 ring-red-100">{{ __('ui.out_of_stock') }}</span>
                                    @elseif($regP && $regP > $minP && !$isRange)
                                        @php($pct = round((1 - $minP / $regP) * 100))
                                        <span class="shrink-0 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-semibold text-green-600 ring-1 ring-green-100">-{{ $pct }}%</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">{{ __('ui.no_products_found') }}</div>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('form[data-auto-submit="products"]');
            const debounceMap = new Map();

            function debounce(key, fn, delay) {
                if (debounceMap.has(key)) {
                    clearTimeout(debounceMap.get(key));
                }
                const t = setTimeout(fn, delay);
                debounceMap.set(key, t);
            }

            forms.forEach(function (form) {
                const inputs = form.querySelectorAll('input[name="q"], input[name="min_price"], input[name="max_price"]');
                const selects = form.querySelectorAll('select');

                selects.forEach(function (el) {
                    el.addEventListener('change', function () {
                        form.submit();
                    });
                });

                inputs.forEach(function (el) {
                    const handler = function () {
                        debounce(el, function () {
                            form.submit();
                        }, 500);
                    };

                    el.addEventListener('input', handler);
                    el.addEventListener('change', handler);
                });
            });
        });
    </script>
</x-site-layout>
