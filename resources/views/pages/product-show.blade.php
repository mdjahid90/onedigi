<x-site-layout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        @php
            $optionGroups = $product->optionGroups ?? collect();
            $variantsAll = ($product->variants ?? collect())->values();
            $variants = $variantsAll->where('is_active', true)->values();
            if ($variants->count() === 0) {
                $variants = $variantsAll;
            }
            $formFields = $product->formFields ?? collect();
            $hasActiveVariants = $variants->count() > 0;
            $hasOptionGroups = $optionGroups->count() > 0 && $hasActiveVariants;

            $minVariantPrice = $hasActiveVariants ? (float) $variants->min('price') : null;
            $maxVariantPrice = $hasActiveVariants ? (float) $variants->max('price') : null;
            $showMin = $minVariantPrice !== null ? $minVariantPrice : (float) $product->price;
            $showMax = $maxVariantPrice !== null ? $maxVariantPrice : (float) $product->price;

            // Stock: check product-level stock; variant stock is checked via JS
            $productStock = $product->stock;
            $productOutOfStock = $productStock !== null && $productStock <= 0 && !$hasActiveVariants;

            $currency = app(\App\Services\CurrencyService::class);
            $rangeLabel = $showMin !== $showMax ? ($currency->format($showMin) . ' – ' . $currency->format($showMax)) : '';
        @endphp
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div
                class="w-full mx-auto bg-white border border-gray-100 rounded-xl overflow-hidden"
                style="max-width: 500px;"
            >
                @if(!empty($product->image))
                    <img
                        src="{{ Storage::url($product->image) }}"
                        alt="{{ $product->title }}"
                        class="w-full object-contain bg-white"
                        style="aspect-ratio: 1 / 1;"
                        width="600"
                        height="600"
                        fetchpriority="high"
                        loading="eager"
                    />
                @else
                    <div class="w-full bg-gray-100" style="aspect-ratio: 1 / 1;"></div>
                @endif
            </div>

            <div>
                <div class="text-xs text-gray-500">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                <h1 class="mt-2 text-xl font-semibold text-gray-900">{{ $product->title }}</h1>
                @if(($reviewsEnabled ?? false) && (int) ($product->reviews_count ?? 0) > 0)
                    <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs text-amber-700 ring-1 ring-amber-100">
                        <span class="text-sm">&#9733;</span>
                        <span class="font-semibold">{{ number_format((float) $product->reviews_avg_rating, 1) }} / 5</span>
                        <span class="text-amber-600">({{ (int) $product->reviews_count }} reviews)</span>
                    </div>
                @elseif($reviewsEnabled ?? false)
                    <div class="mt-2 text-xs text-gray-500">No ratings yet.</div>
                @endif

                {{-- Price display --}}
                <div class="mt-3 flex items-baseline gap-3 flex-wrap {{ $hasOptionGroups ? 'hidden' : '' }}" id="price-block">
                    @if(!$hasActiveVariants && $product->regular_price && (float)$product->regular_price > (float)$product->price)
                        <span class="text-lg line-through" style="color: #f87171; text-decoration-color: #f87171;" id="regular-price-display"><x-money :amount="(float) $product->regular_price" /></span>
                    @else
                        <span class="text-lg line-through hidden" style="color: #f87171; text-decoration-color: #f87171;" id="regular-price-display"></span>
                    @endif
                    <div
                        class="text-lg font-semibold text-red-600"
                        id="product-price"
                        data-price-display="1"
                        data-base-price="{{ $showMin }}"
                        data-range="{{ $rangeLabel }}"
                        data-currency-factor="{{ $currency->factor() }}"
                        data-currency-symbol="{{ $currency->symbol() }}"
                        data-currency-symbol-pos="{{ $currency->symbolPosition() }}"
                    >
                        @if($showMin !== $showMax)
                            <x-money :amount="$showMin" /> – <x-money :amount="$showMax" />
                        @else
                            <x-money :amount="$showMin" />
                        @endif
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-700 whitespace-pre-line">{{ $product->description }}</div>

                <form method="POST" action="{{ route('cart.add', $product) }}" class="w-full" id="product-purchase-form">
                    @csrf
                    <input type="hidden" name="variant_id" value="" id="variant-id" />

                    @if($hasOptionGroups)
                        @php
                            $variantsJson = json_encode($variants->map(function ($v) {
                                return [
                                    'id'            => $v->id,
                                    'price'         => (float) $v->price,
                                    'regular_price' => $v->regular_price ? (float) $v->regular_price : null,
                                    'stock'         => $v->stock,
                                    'options'       => (array) ($v->options ?? []),
                                ];
                            })->values()->all(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

                            $exclusiveGroups = $optionGroups->filter(fn ($g) => ($g->mode ?? 'normal') === 'exclusive')->values();
                            $normalGroups = $optionGroups->reject(fn ($g) => ($g->mode ?? 'normal') === 'exclusive')->values();
                            $useExclusiveUi = $exclusiveGroups->count() > 0 && $normalGroups->count() === 0;

                            $exclusiveDurationValues = collect();
                            $exclusiveDurationLabel = trim((string) ($product->exclusive_duration_label ?? '')) ?: 'Duration';
                            $exclusiveAccountLabel = trim((string) ($product->exclusive_account_label ?? '')) ?: 'Account Type';
                            if ($useExclusiveUi) {
                                $exclusiveDurationValues = $exclusiveGroups
                                    ->flatMap(function ($g) {
                                        $vals = ($g->values ?? collect());
                                        $activeVals = $vals->where('is_active', true);
                                        return $activeVals->count() > 0 ? $activeVals : $vals;
                                    })
                                    ->values();

                                $seen = [];
                                $exclusiveDurationValues = $exclusiveDurationValues->filter(function ($v) use (&$seen) {
                                    $k = (string) ($v->value ?? '');
                                    if ($k === '' || isset($seen[$k])) return false;
                                    $seen[$k] = true;
                                    return true;
                                })->values();
                            }
                        @endphp
                        <div class="mt-6 space-y-4" id="product-options" data-variants='{{ $variantsJson }}' {{ $useExclusiveUi ? 'data-exclusive-ui=1' : '' }}>
                            @if($useExclusiveUi)
                                <div class="space-y-2" data-exclusive-duration>
                                    <div class="flex items-center gap-1">
                                        <span class="text-sm font-semibold text-gray-900">{{ $exclusiveDurationLabel }}</span>
                                        <span class="text-sm font-medium text-gray-500" data-selected-label></span>
                                    </div>
                                    <div class="flex flex-wrap gap-2" role="group" aria-label="{{ $exclusiveDurationLabel }}">
                                        @foreach($exclusiveDurationValues as $value)
                                            <button type="button" class="px-3 py-2 rounded-md border border-gray-200 text-sm font-semibold text-gray-900 hover:border-gray-400 transition-colors data-[active=true]:border-gray-900 data-[active=true]:ring-1 data-[active=true]:ring-gray-900 disabled:opacity-40 disabled:cursor-not-allowed" data-exclusive-duration-btn data-value="{{ $value->value }}">
                                                {{ $value->label }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="space-y-2" data-exclusive-account>
                                    <div class="flex items-center gap-1">
                                        <span class="text-sm font-semibold text-gray-900">{{ $exclusiveAccountLabel }}</span>
                                        <span class="text-sm font-medium text-gray-500" data-selected-label></span>
                                    </div>
                                    <div class="flex flex-wrap gap-2" role="group" aria-label="{{ $exclusiveAccountLabel }}">
                                        @foreach($exclusiveGroups as $group)
                                            <button type="button" class="px-3 py-2 rounded-md border border-gray-200 text-sm font-semibold text-gray-900 hover:border-gray-400 transition-colors data-[active=true]:border-gray-900 data-[active=true]:ring-1 data-[active=true]:ring-gray-900 disabled:opacity-40 disabled:cursor-not-allowed" data-exclusive-account-btn data-key="{{ $group->key }}">
                                                {{ $group->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                @foreach($optionGroups as $group)
                                    <div class="space-y-2" data-option-group data-group-key="{{ $group->key }}" data-group-mode="{{ $group->mode ?? 'normal' }}">
                                        <div class="flex items-center gap-1">
                                            <span class="text-sm font-semibold text-gray-900">{{ $group->name }}</span>
                                            <span class="text-sm font-medium text-gray-500" data-selected-label></span>
                                        </div>
                                        <div class="flex flex-wrap gap-2" role="group" aria-label="{{ $group->name }}">
                                            @php
                                                $groupValues = ($group->values ?? collect());
                                                $activeValues = $groupValues->where('is_active', true);
                                                $renderValues = $activeValues->count() > 0 ? $activeValues : $groupValues;
                                            @endphp
                                            @foreach($renderValues as $value)
                                                <button type="button" class="px-3 py-2 rounded-md border border-gray-200 text-sm font-semibold text-gray-900 hover:border-gray-400 transition-colors data-[active=true]:border-gray-900 data-[active=true]:ring-1 data-[active=true]:ring-gray-900 disabled:opacity-40 disabled:cursor-not-allowed" data-option-value data-value="{{ $value->value }}">
                                                    {{ $value->label }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            <button type="button" class="text-xs text-gray-500 hover:text-gray-700 underline" data-clear-options>Clear</button>
                        </div>
                    @endif

                    {{-- Inline price shown after variant selected --}}
                    <div id="form-price-block" class="mt-5 hidden">
                        <div class="flex items-baseline gap-3 flex-wrap">
                            <span class="text-lg line-through hidden" style="color: #f87171; text-decoration-color: #f87171;" id="form-regular-price"></span>
                            <div class="text-2xl font-bold text-red-600" id="form-selected-price"></div>
                        </div>
                    </div>

                    @if(($formFields->count() ?? 0) > 0)
                        <div class="mt-6 space-y-4" id="product-form-fields">
                            @foreach($formFields as $field)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        {{ $field->label }}
                                        @if($field->is_required)<span class="text-red-500">*</span>@endif
                                    </label>
                                    @if($field->type === 'textarea')
                                        <textarea
                                            name="pf[{{ $field->key }}]"
                                            @if($field->is_required) required @endif
                                            placeholder="{{ $field->placeholder }}"
                                            rows="3"
                                            class="textarea textarea-bordered w-full mt-1 rounded-lg"
                                            data-pf-input="1"
                                            data-pf-required="{{ $field->is_required ? '1' : '0' }}"
                                        ></textarea>
                                    @else
                                        <input
                                            name="pf[{{ $field->key }}]"
                                            type="{{ $field->type }}"
                                            @if($field->is_required) required @endif
                                            placeholder="{{ $field->placeholder }}"
                                            class="input input-bordered w-full mt-1 rounded-lg"
                                            data-pf-input="1"
                                            data-pf-required="{{ $field->is_required ? '1' : '0' }}"
                                        />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Stock / Out of stock --}}
                    @if($productOutOfStock)
                        <div class="mt-4 inline-block px-3 py-1 rounded-full bg-red-50 border border-red-200 text-red-600 text-xs font-medium">Out of Stock</div>
                    @else
                        <div id="stock-status" class="mt-4 hidden"></div>
                    @endif

                    {{-- Price summary table --}}
                    <div id="price-summary" class="mt-4 hidden border border-gray-100 rounded-lg overflow-hidden text-sm">
                        <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-100">
                            <span class="text-gray-600">Product Price <span class="font-semibold text-gray-900" id="summary-unit-price"></span> × 1</span>
                            <span class="font-semibold text-gray-900" id="summary-subtotal"></span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="font-semibold text-gray-900">Total</span>
                            <span class="font-bold text-gray-900" id="summary-total"></span>
                        </div>
                    </div>

                    <div id="cta-block" class="product-cta-panel mt-5 {{ (!$productOutOfStock && $hasOptionGroups) ? 'is-waiting' : '' }}">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button
                                type="submit"
                                formaction="{{ route('buy_now', $product) }}"
                                id="btn-buy-now"
                                {{ $productOutOfStock ? 'disabled' : ($hasOptionGroups ? 'disabled' : '') }}
                                class="product-cta-btn product-cta-primary"
                                data-simple-buy="1"
                            >
                                <span class="product-cta-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M4 12h15" />
                                    </svg>
                                </span>
                                <span class="product-cta-copy">
                                    <span class="product-cta-title">Buy Now</span>
                                    <span class="product-cta-subtitle">Instant checkout</span>
                                </span>
                            </button>

                            <button
                                type="submit"
                                id="btn-add-to-cart"
                                {{ $productOutOfStock ? 'disabled' : ($hasOptionGroups ? 'disabled' : '') }}
                                class="product-cta-btn product-cta-secondary"
                            >
                                <span class="product-cta-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h2l2.2 10.3a2 2 0 002 1.6h7.8a2 2 0 001.9-1.4L21 7H6.2" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20h.01M17 20h.01" />
                                    </svg>
                                </span>
                                <span class="product-cta-copy">
                                    <span class="product-cta-title">Add to Cart</span>
                                    <span class="product-cta-subtitle">Save for later</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-6 text-xs text-gray-600 bg-white border border-gray-100 rounded-lg p-4">
                    Product will be delivered by Email &amp; Dashboard within 10min – 12 hours.
                </div>
            </div>
        </div>

        @if($reviewsEnabled ?? false)
            <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-gray-100 rounded-xl p-5">
                    <h3 class="text-base font-semibold text-gray-900">Write a Review</h3>
                    <div class="mt-1 text-xs text-gray-500">Your review will be published after admin approval.</div>

                    <form method="POST" action="{{ route('products.reviews.store', $product) }}" class="mt-4 space-y-4" id="product-review-form" data-auth="{{ auth()->check() ? '1' : '0' }}">
                        @csrf
                        <input type="hidden" name="name" id="review-name" value="{{ old('name', auth()->user()->name ?? '') }}">
                        <input type="hidden" name="email" id="review-email" value="{{ old('email', auth()->user()->email ?? '') }}">
                        <input type="hidden" name="rating" id="review-rating" value="{{ old('rating') }}">

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rating</label>
                            <div class="mt-2 flex items-center gap-1" id="review-stars" role="radiogroup" aria-label="Review rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <button
                                        type="button"
                                        data-review-star="{{ $i }}"
                                        class="review-star-btn h-9 w-9 rounded-md text-2xl leading-none transition focus:outline-none focus:ring-2 focus:ring-yellow-300"
                                        aria-label="{{ $i }} star"
                                    >&#9733;</button>
                                @endfor
                                <span class="ml-2 text-xs text-gray-500" id="review-rating-label">Select stars</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Review</label>
                            <textarea
                                name="review"
                                rows="5"
                                class="textarea textarea-bordered w-full mt-1 rounded-lg"
                                placeholder="Write your honest opinion about this product..."
                                required
                            >{{ old('review') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-lg">Submit Review</button>
                    </form>

                    <div id="guest-review-modal" class="fixed inset-0 z-[100] hidden">
                        <div class="absolute inset-0 bg-black/40" data-guest-review-close></div>
                        <div class="absolute inset-0 flex items-center justify-center p-4">
                            <div class="w-full max-w-md rounded-xl bg-white border border-gray-200 shadow-xl p-5">
                                <h4 class="text-base font-semibold text-gray-900">Add your details</h4>
                                <p class="mt-1 text-xs text-gray-500">To submit your review, please enter your name and email.</p>

                                <div class="mt-4 space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Name</label>
                                        <input type="text" id="guest-review-name-input" class="input input-bordered w-full mt-1 rounded-lg" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" id="guest-review-email-input" class="input input-bordered w-full mt-1 rounded-lg" />
                                    </div>
                                </div>

                                <div class="mt-5 flex items-center justify-end gap-2">
                                    <button type="button" class="btn btn-ghost rounded-lg" data-guest-review-close>Cancel</button>
                                    <button type="button" class="btn btn-primary rounded-lg" id="guest-review-continue-btn">Continue</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-base font-semibold text-gray-900">Customer Reviews</h3>
                        <div class="text-xs text-gray-500">
                            {{ (int) ($product->reviews_count ?? 0) }} total
                        </div>
                    </div>

                    @php($approvedReviews = $product->approvedReviews ?? collect())
                    <div class="mt-4 space-y-4">
                        @forelse($approvedReviews as $review)
                            <div class="border border-gray-100 rounded-lg p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $review->name }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $review->created_at?->format('M d, Y') }}</div>
                                    </div>
                                    <div class="text-xs font-semibold review-stars-display">
                                        {!! str_repeat('&#9733;', (int) $review->rating) !!}{!! str_repeat('&#9734;', 5 - (int) $review->rating) !!}
                                    </div>
                                </div>
                                <p class="mt-3 text-sm text-gray-700 whitespace-pre-line">{{ $review->review }}</p>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No approved reviews yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .review-star-btn {
            color: #facc15;
            opacity: 0.35;
        }
        .review-star-btn:hover {
            opacity: 0.85;
        }
        .review-star-btn.is-active {
            opacity: 1;
            color: #eab308;
        }
        .review-stars-display {
            color: #eab308;
            letter-spacing: 1px;
        }

        .product-cta-panel {
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96));
            padding: 14px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            transition: opacity .2s ease, filter .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .product-cta-panel.is-waiting {
            opacity: .55;
            filter: grayscale(.15);
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        }
        .product-cta-btn {
            width: 100%;
            min-height: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            border-radius: 14px;
            border: 1px solid transparent;
            padding: 11px 16px;
            text-align: left;
            font-weight: 700;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease, color .18s ease, opacity .18s ease;
        }
        .product-cta-btn:not(:disabled):hover {
            transform: translateY(-1px);
        }
        .product-cta-btn:disabled {
            cursor: not-allowed;
            opacity: .62;
        }
        .product-cta-primary {
            background: linear-gradient(135deg, #111827, #273449);
            color: #ffffff;
            box-shadow: 0 14px 28px rgba(17, 24, 39, 0.22);
        }
        .product-cta-primary:not(:disabled):hover {
            box-shadow: 0 18px 34px rgba(17, 24, 39, 0.28);
        }
        .product-cta-secondary {
            background: #ffffff;
            color: #111827;
            border-color: #dbe3ef;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.07);
        }
        .product-cta-secondary:not(:disabled):hover {
            border-color: #94a3b8;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.10);
        }
        .product-cta-icon {
            display: inline-flex;
            width: 36px;
            height: 36px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(255,255,255,.14);
        }
        .product-cta-secondary .product-cta-icon {
            background: #f1f5f9;
            color: #0f172a;
        }
        .product-cta-icon svg {
            width: 19px;
            height: 19px;
        }
        .product-cta-copy {
            min-width: 0;
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }
        .product-cta-title {
            font-size: 14px;
            letter-spacing: .01em;
            text-transform: uppercase;
        }
        .product-cta-subtitle {
            margin-top: 3px;
            font-size: 11px;
            font-weight: 600;
            opacity: .72;
            text-transform: none;
        }
        @media (max-width: 640px) {
            .product-cta-btn {
                min-height: 54px;
                padding: 10px 12px;
                gap: 10px;
            }
            .product-cta-icon {
                width: 32px;
                height: 32px;
                border-radius: 10px;
            }
            .product-cta-title {
                font-size: 12px;
            }
            .product-cta-subtitle {
                font-size: 10px;
            }
        }

        /* Old price strikethrough - red line through middle */
        #regular-price-display,
        #form-regular-price {
            position: relative;
            display: inline-block;
            text-decoration: none !important;
        }
        #regular-price-display::after,
        #form-regular-price::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 45%;
            height: 2px;
            background: #ef4444;
            pointer-events: none;
        }

        /* Disabled option buttons - X cross */
        button[disabled][data-option-value],
        button[disabled][data-exclusive-duration-btn],
        button[disabled][data-exclusive-account-btn] {
            position: relative;
        }

        button[disabled][data-option-value]::after,
        button[disabled][data-exclusive-duration-btn]::after,
        button[disabled][data-exclusive-account-btn]::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            border-radius: inherit;
            background:
                linear-gradient(45deg, transparent 48%, rgba(239, 68, 68, 0.70) 48%, rgba(239, 68, 68, 0.70) 52%, transparent 52%),
                linear-gradient(-45deg, transparent 48%, rgba(239, 68, 68, 0.70) 48%, rgba(239, 68, 68, 0.70) 52%, transparent 52%);
        }

        button[data-active="true"][data-option-value],
        button[data-active="true"][data-exclusive-duration-btn],
        button[data-active="true"][data-exclusive-account-btn] {
            background: #111827;
            color: #ffffff;
            border-color: #111827;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }

        button[data-active="true"][data-option-value]:hover,
        button[data-active="true"][data-exclusive-duration-btn]:hover,
        button[data-active="true"][data-exclusive-account-btn]:hover {
            background: #0b1220;
        }

        .truck-button {
          --color: #fff;
          --background: #2B3044;
          --tick: #16BF78;
          --base: #0D0F18;
          --wheel: #2B3044;
          --wheel-inner: #646B8C;
          --wheel-dot: #fff;
          --back: #6D58FF;
          --back-inner: #362A89;
          --back-inner-shadow: #2D246B;
          --front: #A6ACCD;
          --front-shadow: #535A79;
          --front-light: #FFF8B1;
          --window: #2B3044;
          --window-shadow: #404660;
          --street: #646B8C;
          --street-fill: #404660;
          --box: #DCB97A;
          --box-shadow: #B89B66;
          padding: 12px 0;
          width: 100%;
          min-height: 52px;
          cursor: pointer;
          text-align: center;
          position: relative;
          border: none;
          outline: none;
          color: var(--color);
          background: var(--background);
          border-radius: var(--br, 15px);
          -webkit-appearance: none;
          -webkit-tap-highlight-color: transparent;
          transform-style: preserve-3d;
          transform: rotateX(var(--rx, 0deg)) translateZ(0);
          transition: transform 0.5s, border-radius 0.3s linear var(--br-d, 0s);
        }
        .truck-button:disabled {
          opacity: .45;
          cursor: not-allowed;
        }
        .truck-button:before,
        .truck-button:after {
          content: "";
          position: absolute;
          left: 0;
          top: 0;
          width: 100%;
          height: 6px;
          display: block;
          background: var(--b, var(--street));
          transform-origin: 0 100%;
          transform: rotateX(90deg) scaleX(var(--sy, 1));
        }
        .truck-button .default,
        .truck-button .success {
          display: block;
          font-weight: 500;
          font-size: 14px;
          line-height: 24px;
          opacity: var(--o, 1);
          transition: opacity 0.3s;
        }
        .truck-button .success {
          --o: 0;
          position: absolute;
          top: 12px;
          left: 0;
          right: 0;
        }
        .truck-button:after {
          --sy: var(--progress, 0);
          --b: var(--street-fill);
        }
        .truck-button .success svg {
          width: 12px;
          height: 10px;
          display: inline-block;
          vertical-align: top;
          fill: none;
          margin: 7px 0 0 12px;
          stroke: var(--tick);
          stroke-width: 2;
          stroke-linecap: round;
          stroke-linejoin: round;
          stroke-dasharray: 16px;
          stroke-dashoffset: var(--offset, 16px);
          transition: stroke-dashoffset 0.4s ease 0.45s;
        }
        .truck-button .truck {
          position: absolute;
          width: 72px;
          height: 28px;
          transform: rotateX(90deg) translate3d(var(--truck-x, 4px), calc(var(--truck-y-n, -26) * 1px), 12px);
        }
        .truck-button .truck:before,
        .truck-button .truck:after {
          content: "";
          position: absolute;
          bottom: -6px;
          left: var(--l, 18px);
          width: 10px;
          height: 10px;
          border-radius: 50%;
          z-index: 2;
          box-shadow: inset 0 0 0 2px var(--wheel), inset 0 0 0 4px var(--wheel-inner);
          background: var(--wheel-dot);
          transform: translateY(calc(var(--truck-y) * -1px)) translateZ(0);
        }
        .truck-button .truck:after {
          --l: 54px;
        }
        .truck-button .truck .wheel,
        .truck-button .truck .wheel:before {
          position: absolute;
          bottom: var(--b, -6px);
          left: var(--l, 6px);
          width: 10px;
          height: 10px;
          border-radius: 50%;
          background: var(--wheel);
          transform: translateZ(0);
        }
        .truck-button .truck .wheel {
          transform: translateY(calc(var(--truck-y) * -1px)) translateZ(0);
        }
        .truck-button .truck .wheel:before {
          --l: 35px;
          --b: 0;
          content: "";
        }
        .truck-button .truck .front,
        .truck-button .truck .back,
        .truck-button .truck .box {
          position: absolute;
        }
        .truck-button .truck .back {
          left: 0;
          bottom: 0;
          z-index: 1;
          width: 47px;
          height: 28px;
          border-radius: 1px 1px 0 0;
          background: linear-gradient(68deg, var(--back-inner) 0%, var(--back-inner) 22%, var(--back-inner-shadow) 22.1%, var(--back-inner-shadow) 100%);
        }
        .truck-button .truck .back:before,
        .truck-button .truck .back:after {
          content: "";
          position: absolute;
        }
        .truck-button .truck .back:before {
          left: 11px;
          top: 0;
          right: 0;
          bottom: 0;
          z-index: 2;
          border-radius: 0 1px 0 0;
          background: var(--back);
        }
        .truck-button .truck .back:after {
          border-radius: 1px;
          width: 73px;
          height: 2px;
          left: -1px;
          bottom: -2px;
          background: var(--base);
        }
        .truck-button .truck .front {
          left: 47px;
          bottom: -1px;
          height: 22px;
          width: 24px;
          -webkit-clip-path: polygon(55% 0, 72% 44%, 100% 58%, 100% 100%, 0 100%, 0 0);
          clip-path: polygon(55% 0, 72% 44%, 100% 58%, 100% 100%, 0 100%, 0 0);
          background: linear-gradient(84deg, var(--front-shadow) 0%, var(--front-shadow) 10%, var(--front) 12%, var(--front) 100%);
        }
        .truck-button .truck .front:before,
        .truck-button .truck .front:after {
          content: "";
          position: absolute;
        }
        .truck-button .truck .front:before {
          width: 7px;
          height: 8px;
          background: #fff;
          left: 7px;
          top: 2px;
          -webkit-clip-path: polygon(0 0, 60% 0%, 100% 100%, 0% 100%);
          clip-path: polygon(0 0, 60% 0%, 100% 100%, 0% 100%);
          background: linear-gradient(59deg, var(--window) 0%, var(--window) 57%, var(--window-shadow) 55%, var(--window-shadow) 100%);
        }
        .truck-button .truck .front:after {
          width: 3px;
          height: 2px;
          right: 0;
          bottom: 3px;
          background: var(--front-light);
        }
        .truck-button .truck .box {
          width: 13px;
          height: 13px;
          right: 56px;
          bottom: 0;
          z-index: 1;
          border-radius: 1px;
          overflow: hidden;
          transform: translate(calc(var(--box-x, -24) * 1px), calc(var(--box-y, -6) * 1px)) scale(var(--box-s, 0.5));
          opacity: var(--box-o, 0);
          background: linear-gradient(68deg, var(--box) 0%, var(--box) 50%, var(--box-shadow) 50.2%, var(--box-shadow) 100%);
          background-size: 250% 100%;
          background-position-x: calc(var(--bx, 0) * 1%);
        }
        .truck-button .truck .box:before,
        .truck-button .truck .box:after {
          content: "";
          position: absolute;
        }
        .truck-button .truck .box:before {
          background: rgba(255, 255, 255, 0.2);
          left: 0;
          right: 0;
          top: 6px;
          height: 1px;
        }
        .truck-button .truck .box:after {
          width: 6px;
          left: 100%;
          top: 0;
          bottom: 0;
          background: var(--back);
          transform: translateX(calc(var(--hx, 0) * 1px));
        }
        .truck-button.animation {
          --rx: -90deg;
          --br: 0;
        }
        .truck-button.animation .default {
          --o: 0;
        }
        .truck-button.animation.done {
          --rx: 0deg;
          --br: 15px;
          --br-d: .2s;
        }
        .truck-button.animation.done .success {
          --o: 1;
          --offset: 0;
        }

        .add-to-cart {
          --background-default: #17171B;
          --background-hover: #0A0A0C;
          --background-scale: 1;
          --text-color: #fff;
          --text-o: 1;
          --text-x: 12px;
          --cart: #fff;
          --cart-x: -48px;
          --cart-y: 0px;
          --cart-rotate: 0deg;
          --cart-scale: .75;
          --cart-clip: 0px;
          --cart-clip-x: 0px;
          --cart-tick-offset: 10px;
          --cart-tick-color: #FF328B;
          --shirt-y: -16px;
          --shirt-scale: 0;
          --shirt-color: #17171B;
          --shirt-logo: #fff;
          --shirt-second-y: 24px;
          --shirt-second-color: #fff;
          --shirt-second-logo: #17171B;
          -webkit-tap-highlight-color: transparent;
          -webkit-appearance: none;
          outline: none;
          background: none;
          border: none;
          padding: 12px 0;
          width: 100%;
          min-height: 52px;
          margin: 0;
          cursor: pointer;
          position: relative;
          font-family: "Inter", Arial, sans-serif;
          border-radius: 5px;
        }
        .add-to-cart:before {
          content: "";
          display: block;
          position: absolute;
          top: 0;
          right: 0;
          bottom: 0;
          left: 0;
          border-radius: 5px;
          transition: background 0.25s;
          background: var(--background, var(--background-default));
          transform: scaleX(var(--background-scale)) translateZ(0);
        }
        .add-to-cart:not(.active):hover {
          --background: var(--background-hover);
        }
        .add-to-cart span {
          display: block;
          text-align: center;
          position: relative;
          z-index: 1;
          font-size: 14px;
          font-weight: 600;
          line-height: 24px;
          color: var(--text-color);
          opacity: var(--text-o);
          transform: translateX(var(--text-x)) translateZ(0);
        }
        .add-to-cart svg {
          display: block;
          width: var(--svg-width, 24px);
          height: var(--svg-height, 24px);
          position: var(--svg-position, relative);
          left: var(--svg-left, 0);
          top: var(--svg-top, 0);
          stroke-linecap: round;
          stroke-linejoin: round;
        }
        .add-to-cart svg path {
          fill: var(--svg-fill, none);
          stroke: var(--svg-stroke, none);
          stroke-width: var(--svg-stroke-width, 2);
        }
        .add-to-cart .morph {
          --svg-width: 64px;
          --svg-height: 13px;
          --svg-left: 50%;
          --svg-top: -12px;
          --svg-position: absolute;
          --svg-fill: var(--background, var(--background-default));
          transition: fill 0.25s;
          pointer-events: none;
          margin-left: -32px;
        }
        .add-to-cart .shirt,
        .add-to-cart .cart {
          pointer-events: none;
          position: absolute;
          left: 50%;
        }
        .add-to-cart .shirt {
          margin: -12px 0 0 -12px;
          top: 0;
          transform-origin: 50% 100%;
          transform: translateY(var(--shirt-y)) scale(var(--shirt-scale));
        }
        .add-to-cart .shirt svg {
          --svg-fill: var(--shirt-color);
        }
        .add-to-cart .shirt svg g {
          --svg-fill: var(--svg-g-fill, var(--shirt-logo));
        }
        .add-to-cart .shirt svg.second {
          --svg-fill: var(--shirt-second-color);
          --svg-g-fill: var(--shirt-second-logo);
          --svg-position: absolute;
          -webkit-clip-path: polygon(0 var(--shirt-second-y), 24px var(--shirt-second-y), 24px 24px, 0 24px);
                  clip-path: polygon(0 var(--shirt-second-y), 24px var(--shirt-second-y), 24px 24px, 0 24px);
        }
        .add-to-cart .cart {
          --svg-width: 36px;
          --svg-height: 26px;
          --svg-stroke: var(--cart);
          top: 10px;
          margin-left: -18px;
          transform: translate(var(--cart-x), var(--cart-y)) rotate(var(--cart-rotate)) scale(var(--cart-scale)) translateZ(0);
        }
        .add-to-cart .cart:before {
          content: "";
          display: block;
          width: 22px;
          height: 12px;
          position: absolute;
          left: 7px;
          top: 7px;
          background: var(--cart);
          -webkit-clip-path: polygon(0 0, 22px 0, calc(22px - var(--cart-clip-x)) var(--cart-clip), var(--cart-clip-x) var(--cart-clip));
                  clip-path: polygon(0 0, 22px 0, calc(22px - var(--cart-clip-x)) var(--cart-clip), var(--cart-clip-x) var(--cart-clip));
        }
        .add-to-cart .cart path.wheel {
          --svg-stroke-width: 1.5;
        }
        .add-to-cart .cart path.tick {
          --svg-stroke: var(--cart-tick-color);
          stroke-dasharray: 10px;
          stroke-dashoffset: var(--cart-tick-offset);
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.4.0/gsap.min.js"></script>
    <script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/16327/MorphSVGPlugin3.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const optionsRoot = document.getElementById('product-options');
            const priceEls = Array.from(document.querySelectorAll('[data-price-display="1"]'));
            const priceEl = priceEls[0] || document.getElementById('product-price');
            const variantIdEl = document.getElementById('variant-id');
            const buyBtn = document.getElementById('btn-buy-now');
            const cartBtn = document.getElementById('btn-add-to-cart');
            const clearBtn = optionsRoot?.querySelector('[data-clear-options]');

            const pfInputs = Array.from(document.querySelectorAll('[data-pf-input="1"]'));

            const basePrice = parseFloat(priceEl?.getAttribute('data-base-price') || '0');
            const rangeLabel = priceEl?.getAttribute('data-range') || '';
            const currencyFactor = parseFloat(priceEl?.getAttribute('data-currency-factor') || '1');
            const currencySymbol = priceEl?.getAttribute('data-currency-symbol') || '৳';
            const currencySymbolPos = priceEl?.getAttribute('data-currency-symbol-pos') || 'suffix';
            const variants = optionsRoot ? JSON.parse(optionsRoot.getAttribute('data-variants') || '[]') : [];
            const groupEls = Array.from(document.querySelectorAll('[data-option-group]'));
            const requiresVariant = optionsRoot && groupEls.length > 0;
            const hasVariants = Array.isArray(variants) && variants.length > 0;

            const isExclusiveUi = optionsRoot?.getAttribute('data-exclusive-ui') === '1';
            const durationRoot = optionsRoot?.querySelector('[data-exclusive-duration]');
            const accountRoot = optionsRoot?.querySelector('[data-exclusive-account]');
            const durationBtns = durationRoot ? Array.from(durationRoot.querySelectorAll('[data-exclusive-duration-btn]')) : [];
            const accountBtns = accountRoot ? Array.from(accountRoot.querySelectorAll('[data-exclusive-account-btn]')) : [];
            const durationLabelEl = durationRoot?.querySelector('[data-selected-label]');
            const accountLabelEl = accountRoot?.querySelector('[data-selected-label]');

            const avail = {};
            if (isExclusiveUi && hasVariants) {
                variants.forEach(function (v) {
                    const opts = v.options || {};
                    Object.keys(opts).forEach(function (k) {
                        const val = String(opts[k] || '');
                        if (!val) return;
                        avail[k] = avail[k] || {};
                        avail[k][val] = true;
                    });
                });
            }

            let exSelectedDuration = '';
            let exSelectedDurationLabel = '';
            let exSelectedAccountKey = '';
            let exSelectedAccountLabel = '';

            const groupMeta = groupEls.map(function (gEl) {
                return {
                    el: gEl,
                    key: gEl.getAttribute('data-group-key') || '',
                    mode: (gEl.getAttribute('data-group-mode') || 'normal').toLowerCase(),
                };
            }).filter(function (g) { return g.key !== ''; });

            const exclusiveKeys = groupMeta.filter(function (g) { return g.mode === 'exclusive'; }).map(function (g) { return g.key; });
            const normalKeys = groupMeta.filter(function (g) { return g.mode !== 'exclusive'; }).map(function (g) { return g.key; });

            function isExclusiveKey(key) {
                return exclusiveKeys.indexOf(key) !== -1;
            }

            function getActiveExclusiveKey() {
                for (let i = 0; i < exclusiveKeys.length; i++) {
                    const k = exclusiveKeys[i];
                    if (selected[k]) return k;
                }
                return '';
            }

            const selected = {};
            const selectedLabels = {};

            function fmtPrice(v) {
                const n = Number(v);
                if (!Number.isFinite(n)) return '';
                const converted = n * currencyFactor;
                const num = converted.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                return currencySymbolPos === 'prefix' ? (currencySymbol + num) : (num + currencySymbol);
            }

            function allSelected() {
                return groupMeta.every(function (g) {
                    return g.key !== '' && typeof selected[g.key] === 'string' && selected[g.key] !== '';
                });
            }

            function matchVariant() {
                if (!requiresVariant || !hasVariants) return null;
                const anySelected = groupMeta.some(function (g) { return selected[g.key]; });
                if (!anySelected) return null;

                const hasAllNormal = normalKeys.every(function (k) { return !!selected[k]; });
                if (!hasAllNormal) return null;

                const activeExclusiveKey = getActiveExclusiveKey();
                return variants.find(function (v) {
                    const opts = v.options || {};
                    const variantKeys = Object.keys(opts).filter(function (k) {
                        return opts[k] !== null && opts[k] !== undefined && String(opts[k]) !== '';
                    });
                    if (variantKeys.length === 0) return false;

                    const variantExclusiveKeys = variantKeys.filter(isExclusiveKey);
                    if (variantExclusiveKeys.length > 1) return false;
                    if (variantExclusiveKeys.length === 1) {
                        if (!activeExclusiveKey) return false;
                        if (variantExclusiveKeys[0] !== activeExclusiveKey) return false;
                    }

                    return variantKeys.every(function (k) {
                        return selected[k] && String(opts[k]) === String(selected[k]);
                    });
                }) || null;
            }

            function matchesPartial(v, exceptKey) {
                const opts = v.options || {};
                const exceptIsExclusive = isExclusiveKey(exceptKey);
                return groupMeta.every(function (g) {
                    const key = g.key;
                    if (key === '' || key === exceptKey) return true;
                    const need = selected[key];
                    if (!need) return true;
                    if (exceptIsExclusive && isExclusiveKey(key)) return true;
                    return String(opts[key] || '') === String(need);
                });
            }

            function isRequiredFilled() {
                return pfInputs.every(function (input) {
                    const required = input.getAttribute('data-pf-required') === '1';
                    if (!required) return true;
                    return String(input.value || '').trim() !== '';
                });
            }

            function updateAvailability() {
                if (isExclusiveUi) {
                    durationBtns.forEach(function (btn) {
                        const val = btn.getAttribute('data-value') || '';
                        btn.dataset.active = (val && val === exSelectedDuration) ? 'true' : 'false';
                    });

                    accountBtns.forEach(function (btn) {
                        const key = btn.getAttribute('data-key') || '';
                        btn.dataset.active = (key && key === exSelectedAccountKey) ? 'true' : 'false';
                    });

                    if (durationLabelEl) {
                        durationLabelEl.textContent = exSelectedDurationLabel ? ': ' + exSelectedDurationLabel : '';
                    }
                    if (accountLabelEl) {
                        accountLabelEl.textContent = exSelectedAccountLabel ? ': ' + exSelectedAccountLabel : '';
                    }

                    durationBtns.forEach(function (btn) {
                        const val = btn.getAttribute('data-value') || '';
                        if (!val) { btn.disabled = true; return; }
                        if (exSelectedAccountKey) {
                            btn.disabled = !(avail[exSelectedAccountKey] && avail[exSelectedAccountKey][val]);
                        } else {
                            btn.disabled = false;
                        }
                    });

                    accountBtns.forEach(function (btn) {
                        const key = btn.getAttribute('data-key') || '';
                        if (!key) { btn.disabled = true; return; }
                        if (exSelectedDuration) {
                            btn.disabled = !(avail[key] && avail[key][exSelectedDuration]);
                        } else {
                            btn.disabled = false;
                        }
                    });

                    const variant = (exSelectedAccountKey && exSelectedDuration)
                        ? (variants.find(function (v) {
                            const opts = v.options || {};
                            return String(opts[exSelectedAccountKey] || '') === String(exSelectedDuration);
                        }) || null)
                        : null;

                    const regularPriceEl  = document.getElementById('regular-price-display');
                    const priceBlockEl    = document.getElementById('price-block');
                    const stockStatusEl   = document.getElementById('stock-status');
                    const formPriceBlock  = document.getElementById('form-price-block');
                    const formPriceVal    = document.getElementById('form-selected-price');
                    const formRegularPr   = document.getElementById('form-regular-price');
                    const ctaBlock        = document.getElementById('cta-block');
                    const priceSummary    = document.getElementById('price-summary');
                    const summaryUnit     = document.getElementById('summary-unit-price');
                    const summarySubtotal = document.getElementById('summary-subtotal');
                    const summaryTotal    = document.getElementById('summary-total');

                    if (variant) {
                        if (priceBlockEl) priceBlockEl.classList.remove('hidden');
                        if (ctaBlock) {
                            ctaBlock.classList.remove('hidden');
                            ctaBlock.classList.remove('is-waiting');
                        }
                        priceEls.forEach(function (el) { el.textContent = fmtPrice(variant.price); });
                        if (variantIdEl) variantIdEl.value = String(variant.id);

                        if (regularPriceEl) {
                            if (variant.regular_price && variant.regular_price > variant.price) {
                                regularPriceEl.textContent = fmtPrice(variant.regular_price);
                                regularPriceEl.classList.remove('hidden');
                            } else {
                                regularPriceEl.textContent = '';
                                regularPriceEl.classList.add('hidden');
                            }
                        }

                        if (formPriceBlock && formPriceVal) {
                            formPriceVal.textContent = fmtPrice(variant.price);
                            if (formRegularPr) {
                                if (variant.regular_price && variant.regular_price > variant.price) {
                                    formRegularPr.textContent = fmtPrice(variant.regular_price);
                                    formRegularPr.classList.remove('hidden');
                                } else {
                                    formRegularPr.textContent = '';
                                    formRegularPr.classList.add('hidden');
                                }
                            }
                            formPriceBlock.classList.remove('hidden');
                        }

                        if (priceSummary && summaryUnit && summarySubtotal && summaryTotal) {
                            const p = fmtPrice(variant.price);
                            summaryUnit.textContent = p;
                            summarySubtotal.textContent = p;
                            summaryTotal.textContent = p;
                            priceSummary.classList.remove('hidden');
                        }

                        const variantStock = variant.stock;
                        const variantOutOfStock = variantStock !== null && variantStock !== undefined && variantStock <= 0;
                        if (stockStatusEl) {
                            if (variantOutOfStock) {
                                stockStatusEl.innerHTML = '<span class="inline-block px-3 py-1 rounded-full bg-red-50 border border-red-200 text-red-600 text-xs font-medium">Out of Stock</span>';
                                stockStatusEl.classList.remove('hidden');
                            } else if (variantStock !== null && variantStock !== undefined && variantStock <= 5) {
                                stockStatusEl.innerHTML = '<span class="inline-block px-3 py-1 rounded-full bg-yellow-50 border border-yellow-200 text-yellow-700 text-xs font-medium">Only ' + variantStock + ' left!</span>';
                                stockStatusEl.classList.remove('hidden');
                            } else {
                                stockStatusEl.classList.add('hidden');
                            }
                        }

                        const canSubmit = true;
                        if (buyBtn) {
                            buyBtn.disabled = !canSubmit;
                            if (canSubmit) buyBtn.removeAttribute('disabled');
                            else buyBtn.setAttribute('disabled', 'disabled');
                        }
                        if (cartBtn) {
                            cartBtn.disabled = !canSubmit;
                            if (canSubmit) cartBtn.removeAttribute('disabled');
                            else cartBtn.setAttribute('disabled', 'disabled');
                        }
                    } else {
                        if (priceBlockEl) {
                            if (requiresVariant) priceBlockEl.classList.add('hidden');
                            else priceBlockEl.classList.remove('hidden');
                        }
                        if (ctaBlock) {
                            ctaBlock.classList.remove('hidden');
                            ctaBlock.classList.add('is-waiting');
                        }
                        priceEls.forEach(function (el) { el.textContent = rangeLabel ? rangeLabel : fmtPrice(basePrice); });
                        if (variantIdEl) variantIdEl.value = '';
                        if (regularPriceEl) { regularPriceEl.textContent = ''; regularPriceEl.classList.add('hidden'); }
                        if (formPriceBlock) formPriceBlock.classList.add('hidden');
                        if (priceSummary) priceSummary.classList.add('hidden');
                        if (stockStatusEl) stockStatusEl.classList.add('hidden');

                        const canSubmit = false;
                        if (buyBtn) {
                            buyBtn.disabled = !canSubmit;
                            if (canSubmit) buyBtn.removeAttribute('disabled');
                            else buyBtn.setAttribute('disabled', 'disabled');
                        }
                        if (cartBtn) {
                            cartBtn.disabled = !canSubmit;
                            if (canSubmit) cartBtn.removeAttribute('disabled');
                            else cartBtn.setAttribute('disabled', 'disabled');
                        }
                    }

                    return;
                }

                groupEls.forEach(function (gEl) {
                    const key = gEl.getAttribute('data-group-key') || '';
                    const selectedLabel = gEl.querySelector('[data-selected-label]');
                    const btns = Array.from(gEl.querySelectorAll('[data-option-value]'));
                    const current = selected[key] || '';
                    const currentLabel = selectedLabels[key] || '';

                    if (selectedLabel) {
                        selectedLabel.textContent = currentLabel ? ': ' + currentLabel : '';
                    }

                    btns.forEach(function (btn) {
                        const val = btn.getAttribute('data-value') || '';
                        const active = val !== '' && current !== '' && String(val) === String(current);
                        btn.dataset.active = active ? 'true' : 'false';

                        if (!requiresVariant || !hasVariants) {
                            btn.disabled = false;
                            return;
                        }

                        const allowed = variants.some(function (v) {
                            if (!matchesPartial(v, key)) return false;
                            return String((v.options || {})[key] || '') === String(val);
                        });
                        btn.disabled = !allowed;
                    });

                    if (current && btns.every(b => String(b.getAttribute('data-value') || '') !== String(current))) {
                        selected[key] = '';
                    }
                });

                const variant = matchVariant();
                const regularPriceEl  = document.getElementById('regular-price-display');
                const priceBlockEl    = document.getElementById('price-block');
                const stockStatusEl   = document.getElementById('stock-status');
                const formPriceBlock  = document.getElementById('form-price-block');
                const formPriceVal    = document.getElementById('form-selected-price');
                const formRegularPr   = document.getElementById('form-regular-price');
                const ctaBlock        = document.getElementById('cta-block');
                const priceSummary    = document.getElementById('price-summary');
                const summaryUnit     = document.getElementById('summary-unit-price');
                const summarySubtotal = document.getElementById('summary-subtotal');
                const summaryTotal    = document.getElementById('summary-total');

                if (variant) {
                    if (priceBlockEl) priceBlockEl.classList.remove('hidden');
                    if (ctaBlock) {
                        ctaBlock.classList.remove('hidden');
                        ctaBlock.classList.remove('is-waiting');
                    }
                    priceEls.forEach(function (el) { el.textContent = fmtPrice(variant.price); });
                    if (variantIdEl) variantIdEl.value = String(variant.id);

                    // Top strikethrough regular price
                    if (regularPriceEl) {
                        if (variant.regular_price && variant.regular_price > variant.price) {
                            regularPriceEl.textContent = fmtPrice(variant.regular_price);
                            regularPriceEl.classList.remove('hidden');
                        } else {
                            regularPriceEl.textContent = '';
                            regularPriceEl.classList.add('hidden');
                        }
                    }

                    // Inline price inside form
                    if (formPriceBlock && formPriceVal) {
                        formPriceVal.textContent = fmtPrice(variant.price);
                        if (formRegularPr) {
                            if (variant.regular_price && variant.regular_price > variant.price) {
                                formRegularPr.textContent = fmtPrice(variant.regular_price);
                                formRegularPr.classList.remove('hidden');
                            } else {
                                formRegularPr.textContent = '';
                                formRegularPr.classList.add('hidden');
                            }
                        }
                        formPriceBlock.classList.remove('hidden');
                    }

                    // Price summary table
                    if (priceSummary && summaryUnit && summarySubtotal && summaryTotal) {
                        const p = fmtPrice(variant.price);
                        summaryUnit.textContent = p;
                        summarySubtotal.textContent = p;
                        summaryTotal.textContent = p;
                        priceSummary.classList.remove('hidden');
                    }

                    // Stock status
                    const variantStock = variant.stock;
                    const variantOutOfStock = variantStock !== null && variantStock !== undefined && variantStock <= 0;
                    if (stockStatusEl) {
                        if (variantOutOfStock) {
                            stockStatusEl.innerHTML = '<span class="inline-block px-3 py-1 rounded-full bg-red-50 border border-red-200 text-red-600 text-xs font-medium">Out of Stock</span>';
                            stockStatusEl.classList.remove('hidden');
                        } else if (variantStock !== null && variantStock !== undefined && variantStock <= 5) {
                            stockStatusEl.innerHTML = '<span class="inline-block px-3 py-1 rounded-full bg-yellow-50 border border-yellow-200 text-yellow-700 text-xs font-medium">Only ' + variantStock + ' left!</span>';
                            stockStatusEl.classList.remove('hidden');
                        } else {
                            stockStatusEl.classList.add('hidden');
                        }
                    }

                    const canSubmit = true;
                    if (buyBtn) {
                        buyBtn.disabled = !canSubmit;
                        if (canSubmit) buyBtn.removeAttribute('disabled');
                        else buyBtn.setAttribute('disabled', 'disabled');
                    }
                    if (cartBtn) {
                        cartBtn.disabled = !canSubmit;
                        if (canSubmit) cartBtn.removeAttribute('disabled');
                        else cartBtn.setAttribute('disabled', 'disabled');
                    }
                } else {
                    if (priceBlockEl) {
                        if (requiresVariant) priceBlockEl.classList.add('hidden');
                        else priceBlockEl.classList.remove('hidden');
                    }
                    if (ctaBlock) {
                        ctaBlock.classList.remove('hidden');
                        if (requiresVariant) ctaBlock.classList.add('is-waiting');
                        else ctaBlock.classList.remove('is-waiting');
                    }
                    priceEls.forEach(function (el) { el.textContent = rangeLabel ? rangeLabel : fmtPrice(basePrice); });
                    if (variantIdEl) variantIdEl.value = '';
                    if (regularPriceEl) { regularPriceEl.textContent = ''; regularPriceEl.classList.add('hidden'); }
                    if (formPriceBlock) formPriceBlock.classList.add('hidden');
                    if (priceSummary) priceSummary.classList.add('hidden');
                    if (stockStatusEl) stockStatusEl.classList.add('hidden');

                    const canSubmit = (!requiresVariant || !!variant) && isRequiredFilled();
                    if (buyBtn) {
                        buyBtn.disabled = !canSubmit;
                        if (canSubmit) buyBtn.removeAttribute('disabled');
                        else buyBtn.setAttribute('disabled', 'disabled');
                    }
                    if (cartBtn) {
                        cartBtn.disabled = !canSubmit;
                        if (canSubmit) cartBtn.removeAttribute('disabled');
                        else cartBtn.setAttribute('disabled', 'disabled');
                    }
                }
            }

            groupMeta.forEach(function (g) {
                const key = g.key;
                g.el.querySelectorAll('[data-option-value]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const val = btn.getAttribute('data-value') || '';
                        if (btn.disabled || key === '' || val === '') return;

                        if (isExclusiveKey(key)) {
                            exclusiveKeys.forEach(function (k) {
                                if (k !== key) {
                                    selected[k] = '';
                                    selectedLabels[k] = '';
                                }
                            });
                        }

                        selected[key] = String(val);
                        selectedLabels[key] = String(btn.textContent || '').trim() || String(val);
                        updateAvailability();
                    });
                });
            });

            if (isExclusiveUi) {
                durationBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const val = btn.getAttribute('data-value') || '';
                        if (!val || btn.disabled) return;
                        exSelectedDuration = String(val);
                        exSelectedDurationLabel = String(btn.textContent || '').trim() || String(val);
                        updateAvailability();
                    });
                });

                accountBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const key = btn.getAttribute('data-key') || '';
                        if (!key || btn.disabled) return;
                        exSelectedAccountKey = String(key);
                        exSelectedAccountLabel = String(btn.textContent || '').trim() || String(key);
                        updateAvailability();
                    });
                });
            }

            pfInputs.forEach(function (input) {
                input.addEventListener('input', function () {
                    updateAvailability();
                });
            });

            clearBtn?.addEventListener('click', function () {
                Object.keys(selected).forEach(function (k) {
                    selected[k] = '';
                });
                Object.keys(selectedLabels).forEach(function (k) {
                    selectedLabels[k] = '';
                });
                exSelectedDuration = '';
                exSelectedDurationLabel = '';
                exSelectedAccountKey = '';
                exSelectedAccountLabel = '';
                updateAvailability();
            });

            if (cartBtn && cartBtn.classList.contains('add-to-cart') && window.gsap && window.MorphSVGPlugin) {
                gsap.registerPlugin(MorphSVGPlugin);

                let morph = cartBtn.querySelector('.morph path');
                let shirt = cartBtn.querySelectorAll('.shirt svg > path');

                cartBtn.addEventListener('pointerdown', function () {
                    if (cartBtn.disabled || cartBtn.classList.contains('active')) {
                        return;
                    }
                    gsap.to(cartBtn, {
                        '--background-scale': .97,
                        duration: .15
                    });
                });

                cartBtn.addEventListener('click', function (event) {
                    if (cartBtn.disabled || cartBtn.classList.contains('active')) {
                        return;
                    }

                    const form = cartBtn.closest('form');
                    if (!form || form.dataset.cartSubmitting === '1') {
                        event.preventDefault();
                        return;
                    }

                    event.preventDefault();
                    form.dataset.cartSubmitting = '1';
                    cartBtn.classList.add('active');

                    gsap.to(cartBtn, {
                        keyframes: [{
                            '--background-scale': .97,
                            duration: .15
                        }, {
                            '--background-scale': 1,
                            delay: .125,
                            duration: 1.2,
                            ease: 'elastic.out(1, .6)'
                        }]
                    });

                    gsap.to(cartBtn, {
                        keyframes: [{
                            '--shirt-scale': 1,
                            '--shirt-y': '-42px',
                            '--cart-x': '0px',
                            '--cart-scale': 1,
                            duration: .4,
                            ease: 'power1.in'
                        }, {
                            '--shirt-y': '-40px',
                            duration: .3
                        }, {
                            '--shirt-y': '16px',
                            '--shirt-scale': .9,
                            duration: .25,
                            ease: 'none'
                        }, {
                            '--shirt-scale': 0,
                            duration: .3,
                            ease: 'none'
                        }]
                    });

                    gsap.to(cartBtn, {
                        '--shirt-second-y': '0px',
                        delay: .835,
                        duration: .12
                    });

                    gsap.to(cartBtn, {
                        keyframes: [{
                            '--cart-clip': '12px',
                            '--cart-clip-x': '3px',
                            delay: .9,
                            duration: .06
                        }, {
                            '--cart-y': '2px',
                            duration: .1
                        }, {
                            '--cart-tick-offset': '0px',
                            '--cart-y': '0px',
                            duration: .2,
                            onComplete() {
                                cartBtn.style.overflow = 'hidden';
                            }
                        }, {
                            '--cart-x': '52px',
                            '--cart-rotate': '-15deg',
                            duration: .2
                        }, {
                            '--cart-x': '104px',
                            '--cart-rotate': '0deg',
                            duration: .2,
                            clearProps: true,
                            onComplete() {
                                cartBtn.style.overflow = 'hidden';
                                cartBtn.style.setProperty('--text-o', 0);
                                cartBtn.style.setProperty('--text-x', '0px');
                                cartBtn.style.setProperty('--cart-x', '-104px');
                            }
                        }, {
                            '--text-o': 1,
                            '--text-x': '12px',
                            '--cart-x': '-48px',
                            '--cart-scale': .75,
                            duration: .25,
                            clearProps: true,
                            onComplete() {
                                cartBtn.classList.remove('active');
                            }
                        }]
                    });

                    gsap.to(cartBtn, {
                        keyframes: [{
                            '--text-o': 0,
                            duration: .3
                        }]
                    });

                    gsap.to(morph, {
                        keyframes: [{
                            morphSVG: 'M0 12C6 12 20 10 32 0C43.9024 9.99999 58 12 64 12V13H0V12Z',
                            duration: .25,
                            ease: 'power1.out'
                        }, {
                            morphSVG: 'M0 12C6 12 17 12 32 12C47.9024 12 58 12 64 12V13H0V12Z',
                            duration: .15,
                            ease: 'none'
                        }]
                    });

                    gsap.to(shirt, {
                        keyframes: [{
                            morphSVG: 'M4.99997 3L8.99997 1.5C8.99997 1.5 10.6901 3 12 3C13.3098 3 15 1.5 15 1.5L19 3L23.5 8L20.5 11L19 9.5L18 22.5C18 22.5 14 21.5 12 21.5C10 21.5 5.99997 22.5 5.99997 22.5L4.99997 9.5L3.5 11L0.5 8L4.99997 3Z',
                            duration: .25,
                            delay: .25
                        }, {
                            morphSVG: 'M4.99997 3L8.99997 1.5C8.99997 1.5 10.6901 3 12 3C13.3098 3 15 1.5 15 1.5L19 3L23.5 8L20.5 11L19 9.5L18.5 22.5C18.5 22.5 13.5 22.5 12 22.5C10.5 22.5 5.5 22.5 5.5 22.5L4.99997 9.5L3.5 11L0.5 8L4.99997 3Z',
                            duration: .85,
                            ease: 'elastic.out(1, .5)'
                        }, {
                            morphSVG: 'M4.99997 3L8.99997 1.5C8.99997 1.5 10.6901 3 12 3C13.3098 3 15 1.5 15 1.5L19 3L22.5 8L19.5 10.5L19 9.5L17.1781 18.6093C17.062 19.1901 16.778 19.7249 16.3351 20.1181C15.4265 20.925 13.7133 22.3147 12 23C10.2868 22.3147 8.57355 20.925 7.66487 20.1181C7.22198 19.7249 6.93798 19.1901 6.82183 18.6093L4.99997 9.5L4.5 10.5L1.5 8L4.99997 3Z',
                            duration: 0,
                            delay: 1.25
                        }]
                    });

                    window.setTimeout(function () {
                        form.requestSubmit(cartBtn);
                    }, 1900);
                });
            }

            if (buyBtn && buyBtn.hasAttribute('data-simple-buy')) {
                buyBtn.addEventListener('click', function () {
                    buyBtn.classList.add('active');
                    window.setTimeout(function () {
                        buyBtn.classList.remove('active');
                    }, 600);
                });
            } else if (buyBtn && buyBtn.classList.contains('truck-button')) {
                buyBtn.addEventListener('click', function (event) {
                    if (buyBtn.disabled) return;

                    const form = buyBtn.closest('form');
                    if (!form) return;
                    if (form.dataset.buySubmitting === '1') return;

                    event.preventDefault();

                    let box = buyBtn.querySelector('.box');
                    let truck = buyBtn.querySelector('.truck');

                    if (!buyBtn.classList.contains('done')) {
                        if (!buyBtn.classList.contains('animation')) {
                            buyBtn.classList.add('animation');

                            gsap.to(buyBtn, {
                                '--box-s': 1,
                                '--box-o': 1,
                                duration: .3,
                                delay: .5
                            });

                            gsap.to(box, {
                                x: 0,
                                duration: .4,
                                delay: .7
                            });

                            gsap.to(buyBtn, {
                                '--hx': -5,
                                '--bx': 50,
                                duration: .18,
                                delay: .92
                            });

                            gsap.to(box, {
                                y: 0,
                                duration: .1,
                                delay: 1.15
                            });

                            gsap.set(buyBtn, {
                                '--truck-y': 0,
                                '--truck-y-n': -26
                            });

                            gsap.to(buyBtn, {
                                '--truck-y': 1,
                                '--truck-y-n': -25,
                                duration: .2,
                                delay: 1.25,
                                onComplete() {
                                    gsap.timeline({
                                        onComplete() {
                                            buyBtn.classList.add('done');
                                            form.dataset.buySubmitting = '1';
                                            form.requestSubmit(buyBtn);
                                        }
                                    }).to(truck, {
                                        x: 0,
                                        duration: .4
                                    }).to(truck, {
                                        x: 40,
                                        duration: 1
                                    }).to(truck, {
                                        x: 20,
                                        duration: .6
                                    }).to(truck, {
                                        x: 96,
                                        duration: .4
                                    });

                                    gsap.to(buyBtn, {
                                        '--progress': 1,
                                        duration: 2.4,
                                        ease: "power2.in"
                                    });
                                }
                            });
                        }
                    }
                });
            }

            updateAvailability();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reviewForm = document.getElementById('product-review-form');
            if (!reviewForm) return;

            const isAuthenticated = reviewForm.dataset.auth === '1';
            const ratingInput = document.getElementById('review-rating');
            const ratingLabel = document.getElementById('review-rating-label');
            const reviewNameInput = document.getElementById('review-name');
            const reviewEmailInput = document.getElementById('review-email');
            const starButtons = Array.from(reviewForm.querySelectorAll('[data-review-star]'));

            const guestModal = document.getElementById('guest-review-modal');
            const guestNameInput = document.getElementById('guest-review-name-input');
            const guestEmailInput = document.getElementById('guest-review-email-input');
            const guestContinueBtn = document.getElementById('guest-review-continue-btn');
            const guestCloseBtns = Array.from(document.querySelectorAll('[data-guest-review-close]'));

            function paintStars(value) {
                const current = Number(value) || 0;
                starButtons.forEach(function (btn) {
                    const starValue = Number(btn.dataset.reviewStar || 0);
                    const active = starValue <= current;
                    btn.classList.toggle('is-active', active);
                    btn.setAttribute('aria-checked', active ? 'true' : 'false');
                });

                if (!ratingLabel) return;
                if (current > 0) {
                    ratingLabel.textContent = current + ' star' + (current > 1 ? 's' : '') + ' selected';
                } else {
                    ratingLabel.textContent = 'Select stars';
                }
            }

            function openGuestModal() {
                if (!guestModal) return;
                if (guestNameInput && reviewNameInput) {
                    guestNameInput.value = reviewNameInput.value || '';
                }
                if (guestEmailInput && reviewEmailInput) {
                    guestEmailInput.value = reviewEmailInput.value || '';
                }
                guestModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeGuestModal() {
                if (!guestModal) return;
                guestModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            starButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const selected = Number(btn.dataset.reviewStar || 0);
                    if (ratingInput) {
                        ratingInput.value = selected > 0 ? String(selected) : '';
                    }
                    paintStars(selected);
                });
            });

            const oldRating = Number(ratingInput?.value || 0);
            if (oldRating > 0) {
                paintStars(oldRating);
            }

            guestCloseBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    closeGuestModal();
                });
            });

            guestContinueBtn?.addEventListener('click', function () {
                const guestName = (guestNameInput?.value || '').trim();
                const guestEmail = (guestEmailInput?.value || '').trim();

                if (!guestName || !guestEmail) {
                    if (window.createToast) {
                        window.createToast('warning', 'Missing info', 'Please enter your name and email.');
                    }
                    return;
                }

                if (reviewNameInput) reviewNameInput.value = guestName;
                if (reviewEmailInput) reviewEmailInput.value = guestEmail;

                closeGuestModal();
                reviewForm.submit();
            });

            reviewForm.addEventListener('submit', function (event) {
                const ratingValue = Number(ratingInput?.value || 0);
                if (ratingValue < 1 || ratingValue > 5) {
                    event.preventDefault();
                    if (window.createToast) {
                        window.createToast('warning', 'Rating required', 'Please select a star rating.');
                    }
                    return;
                }

                if (isAuthenticated) {
                    return;
                }

                const hasGuestInfo = !!((reviewNameInput?.value || '').trim()) && !!((reviewEmailInput?.value || '').trim());
                if (!hasGuestInfo) {
                    event.preventDefault();
                    openGuestModal();
                }
            });
        });
    </script>
</x-site-layout>
