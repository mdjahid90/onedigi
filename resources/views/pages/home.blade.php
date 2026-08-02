<x-site-layout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="-mx-4 sm:-mx-6 lg:-mx-8">
                <div class="bg-white border border-gray-100 rounded-xl overflow-hidden px-0">
                @if(isset($banners) && $banners->count())
                    <div x-data="{
                            index: 0,
                            total: {{ $banners->count() }},
                            intervalMs: 4000,
                            timer: null,
                            width: 0,
                            updateWidth() {
                                this.width = this.$refs.viewport ? this.$refs.viewport.clientWidth : 0;
                            },
                            start() {
                                this.updateWidth();
                                if (this.timer || this.total <= 1) return;
                                this.timer = setInterval(() => this.next(), this.intervalMs);
                            },
                            stop() {
                                if (!this.timer) return;
                                clearInterval(this.timer);
                                this.timer = null;
                            },
                            next() {
                                this.index = (this.index + 1) % this.total;
                            },
                            goTo(i) {
                                this.index = i;
                            }
                        }"
                         x-init="updateWidth(); start(); window.addEventListener('resize', () => updateWidth())"
                         @mouseenter="stop()"
                         @mouseleave="start()"
                         class="relative overflow-hidden bg-gray-100"
                         x-cloak>
                        <div x-ref="viewport" class="w-full overflow-hidden">
                            <div class="flex transition-transform duration-700 ease-out will-change-transform"
                                 :style="`transform: translateX(-${index * width}px)`">
                            @foreach($banners as $i => $banner)
                                <a href="{{ $banner->link ?: '#' }}" class="block w-full shrink-0 bg-gray-100">
                                    @if(!empty($banner->image))
                                        <img
                                            src="{{ Storage::url($banner->image) }}"
                                            alt="{{ $banner->title }}"
                                            class="block w-full h-auto object-contain bg-gray-100"
                                            width="1280"
                                            height="448"
                                            @if($i === 0) fetchpriority="high" loading="eager" @else loading="lazy" decoding="async" @endif
                                        />
                                    @else
                                        <div class="w-full min-h-[160px] bg-gray-100"></div>
                                    @endif
                                </a>
                            @endforeach
                            </div>
                        </div>

                        <div class="absolute inset-x-0 bottom-3 flex items-center justify-center gap-2">
                            @foreach($banners as $i => $banner)
                                <button type="button"
                                        class="h-2.5 w-2.5 rounded-full"
                                        :class="index === {{ $i }} ? 'bg-white' : 'bg-white/60'"
                                        @click="goTo({{ $i }})"
                                        aria-label="Go to slide {{ $i + 1 }}"></button>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="w-full min-h-[160px] bg-gray-100"></div>
                @endif
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-base font-semibold text-gray-900">{{ __('ui.featured_categories') }}</h2>
                <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @forelse($categories as $category)
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="bg-white border border-gray-100 rounded-lg p-4 hover:border-gray-200">
                            <div class="flex items-center gap-3">
                                @if(!empty($category->icon))
                                    <img src="{{ Storage::url($category->icon) }}" alt="{{ $category->name }}" class="h-8 w-8 rounded-md object-cover border border-gray-100" width="32" height="32" loading="lazy" decoding="async" />
                                @else
                                    <div class="h-8 w-8 rounded-md bg-gray-100 border border-gray-100"></div>
                                @endif
                                <div class="text-sm font-semibold text-gray-900">{{ $category->name }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="text-sm text-gray-500">No categories yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('ui.trending_products') }}</h2>
                    <a href="{{ route('products.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __('ui.view_all') }}</a>
                </div>
                <div class="mt-3 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @forelse($trendingProducts as $product)
                        <div class="group bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:border-gray-200 transition">
                            <a href="{{ route('products.show', $product) }}" class="block bg-gray-50" aria-label="View {{ $product->title }}">
                                @if(!empty($product->image))
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->title }}" class="aspect-square w-full object-cover bg-gray-100 transition-transform duration-300 group-hover:scale-[1.02]" width="320" height="320" loading="lazy" decoding="async" />
                                @else
                                    <div class="aspect-square bg-gray-100"></div>
                                @endif
                            </a>
                            <div class="p-3 sm:p-4">
                                <div class="text-xs text-gray-500">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                                <a href="{{ route('products.show', $product) }}" class="mt-1 block text-sm font-semibold text-gray-900 hover:text-indigo-600">{{ $product->title }}</a>
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
                                                <x-money :amount="$minP" /> - <x-money :amount="$maxP" />
                                            @else
                                                <x-money :amount="$minP" />
                                            @endif
                                        </div>
                                    </div>
                                    @if($cardOutOfStock)
                                        <span class="shrink-0 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-500 ring-1 ring-red-100">Out of Stock</span>
                                    @elseif($regP && $regP > $minP && !$isRange)
                                        @php($pct = round((1 - $minP / $regP) * 100))
                                        <span class="shrink-0 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-semibold text-green-600 ring-1 ring-green-100">-{{ $pct }}%</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">No products yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-site-layout>

