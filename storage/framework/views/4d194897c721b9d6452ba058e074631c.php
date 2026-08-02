<header
    x-data="{ menuOpen: false, cartOpen: false }"
    x-on:keydown.escape.window="menuOpen = false; cartOpen = false"
    x-effect="document.body.classList.toggle('overflow-hidden', menuOpen || cartOpen)"
    class="fixed top-0 inset-x-0 z-50 bg-white/70 backdrop-blur border-b border-white/40 shadow-lg">
    <?php
        $logoLight = \App\Models\Setting::getValue('logo_light', '');
        $siteName = trim((string) config('app.name')) ?: 'OneDigify';
        $locale = app()->getLocale();
        $langLabel = strtoupper($locale);
        $currencyService = app(\App\Services\CurrencyService::class);
        $currencyCode = $currencyService->code();
        $cart = session('cart', []);
        $cartCount = 0;
        $cartDrawerItems = [];
        $cartDrawerTotal = 0;
        if (is_array($cart)) {
            foreach ($cart as $row) {
                if (is_array($row)) {
                    $cartCount += (int) ($row['quantity'] ?? 0);
                } else {
                    $cartCount += (int) $row;
                }
            }

            $cartProductIds = [];
            foreach ($cart as $cartKey => $row) {
                $cartProductIds[] = is_array($row) ? (int) ($row['product_id'] ?? $cartKey) : (int) $cartKey;
            }
            $cartProductIds = array_values(array_unique(array_filter($cartProductIds)));
            $cartProducts = count($cartProductIds) > 0
                ? \App\Models\Product::query()
                    ->with('category')
                    ->whereIn('id', $cartProductIds)
                    ->where('is_active', true)
                    ->get()
                    ->keyBy('id')
                : collect();

            foreach ($cart as $cartKey => $row) {
                $productId = is_array($row) ? (int) ($row['product_id'] ?? $cartKey) : (int) $cartKey;
                $product = $cartProducts->get($productId);

                if (!$product) {
                    continue;
                }

                $quantity = is_array($row) ? max(1, (int) ($row['quantity'] ?? 1)) : max(1, (int) $row);
                $unitPrice = is_array($row) ? (float) ($row['unit_price'] ?? $product->price) : (float) $product->price;
                $subtotal = $unitPrice * $quantity;
                $cartDrawerTotal += $subtotal;

                $cartDrawerItems[] = [
                    'cart_key' => (string) $cartKey,
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'meta' => is_array($row) ? ($row['meta'] ?? []) : [],
                ];
            }
        }
        $cartBadge = $cartCount > 99 ? '99+' : (string) $cartCount;
        $siteUser = auth()->user();
        $userNotificationsReady = $siteUser && \Illuminate\Support\Facades\Schema::hasTable('user_notifications');
        $userUnreadNotifications = $userNotificationsReady
            ? \App\Models\UserNotification::query()->where('user_id', $siteUser->id)->whereNull('read_at')->count()
            : 0;
        $userRecentNotifications = $userNotificationsReady
            ? \App\Models\UserNotification::query()->where('user_id', $siteUser->id)->latest()->take(8)->get()
            : collect();
    ?>
    <style>
        .site-header-actions {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .site-header-cart-badge {
            top: -0.3rem;
            right: -0.3rem;
        }

        @media (max-width: 767.98px) {
            .site-header-search {
                flex: 0 0 auto !important;
                margin-left: auto !important;
                margin-right: 0.25rem !important;
                max-width: none !important;
            }

            .site-header-actions {
                position: static !important;
                transform: none !important;
                flex: 0 0 auto;
                gap: 0.25rem !important;
            }

            .site-header-search-button,
            .site-header-actions .site-header-icon-button {
                width: 2rem !important;
                height: 2rem !important;
                border: 1px solid rgba(226, 232, 240, 0.95) !important;
                border-radius: 9999px !important;
                background: rgba(255, 255, 255, 0.92) !important;
                box-shadow: 0 6px 16px rgba(15, 23, 42, 0.1) !important;
            }

            .site-header-search-button svg,
            .site-header-actions .site-header-icon-button svg {
                width: 1rem !important;
                height: 1rem !important;
            }

            .site-header-login-link {
                height: 2rem !important;
                padding-left: 0.65rem !important;
                padding-right: 0.65rem !important;
                font-size: 0.75rem !important;
            }

            .site-header-brand {
                max-width: calc(100vw - 14.5rem);
                overflow: hidden;
            }

            .site-header-brand img {
                max-width: 9rem;
            }

            .site-header-cart-badge {
                top: 0.125rem !important;
                right: 0.125rem !important;
                transform: none;
                min-width: 0.5rem !important;
                width: 0.5rem !important;
                height: 0.5rem !important;
                padding: 0 !important;
                font-size: 0 !important;
                line-height: 1 !important;
                border: 1px solid white !important;
                box-shadow: none !important;
            }

            .site-header-cart-button {
                border-color: rgba(226, 232, 240, 0.95) !important;
                background: rgba(255, 255, 255, 0.92) !important;
                box-shadow: 0 6px 16px rgba(15, 23, 42, 0.1) !important;
            }

            .site-header-notification-panel {
                position: fixed !important;
                left: 50% !important;
                right: auto !important;
                top: 4.25rem !important;
                width: min(calc(100vw - 2rem), 340px) !important;
                max-height: calc(100vh - 6rem) !important;
                transform: translateX(-50%) !important;
            }
        }

        @media (max-width: 420px) {
            .site-header-cart-badge {
                width: 0.45rem !important;
                height: 0.45rem !important;
            }
        }
    </style>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="h-14 flex items-center gap-2">
            <div class="site-header-brand ml-12 sm:ml-14 md:ml-16 flex items-center gap-3">
                <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-2" aria-label="<?php echo e($siteName); ?> home">
                    <?php if(!empty($logoLight)): ?>
                        <img src="<?php echo e(Storage::url($logoLight)); ?>" alt="Logo" class="block h-8 w-auto" width="144" height="32" fetchpriority="high" decoding="async" />
                    <?php else: ?>
                        <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'block h-8 w-auto fill-current text-gray-900']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'block h-8 w-auto fill-current text-gray-900']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                    <?php endif; ?>
                </a>
            </div>

            <div
                x-data='{
                    query: "",
                    open: false,
                    loading: false,
                    mobileSearchOpen: false,
                    products: [],
                    debounceTimer: null,
                    aborter: null,
                    suggestUrl: <?php echo json_encode(route("products.suggest"), 15, 512) ?>,
                    productsUrl: <?php echo json_encode(route("products.index"), 15, 512) ?>,
                    onInput() {
                        clearTimeout(this.debounceTimer);
                        if (!this.query.trim()) {
                            this.products = [];
                            this.open = false;
                            this.loading = false;
                            return;
                        }
                        this.loading = true;
                        this.open = true;
                        this.debounceTimer = setTimeout(() => this.fetchProducts(), 180);
                    },
                    async fetchProducts() {
                        const q = this.query.trim();
                        if (!q) {
                            this.products = [];
                            this.loading = false;
                            this.open = false;
                            return;
                        }
                        if (this.aborter) {
                            this.aborter.abort();
                        }
                        this.aborter = new AbortController();
                        try {
                            const response = await fetch(`${this.suggestUrl}?q=${encodeURIComponent(q)}`, {
                                headers: { "Accept": "application/json" },
                                signal: this.aborter.signal,
                            });
                            if (!response.ok) {
                                throw new Error("Search failed");
                            }
                            const payload = await response.json();
                            this.products = Array.isArray(payload.products) ? payload.products : [];
                            this.open = true;
                        } catch (error) {
                            if (error.name !== "AbortError") {
                                this.products = [];
                                this.open = true;
                            }
                        } finally {
                            this.loading = false;
                        }
                    },
                    submitSearch() {
                        const q = this.query.trim();
                        if (!q) return;
                        window.location.href = `${this.productsUrl}?q=${encodeURIComponent(q)}`;
                    }
                }'
                @click.outside="open = false"
                @keydown.escape.window="mobileSearchOpen = false; open = false"
                x-effect="document.body.classList.toggle('overflow-hidden', mobileSearchOpen || menuOpen)"
                class="site-header-search relative ml-auto mr-1 sm:mx-2 md:mx-4 sm:flex-1 sm:max-w-[280px] md:max-w-[380px] lg:max-w-[470px] xl:max-w-[560px]">
                <button
                    type="button"
                    @click="mobileSearchOpen = true; open = false; $nextTick(() => $refs.mobileSearchInput?.focus())"
                    class="site-header-search-button sm:hidden inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-700 shadow-sm transition hover:bg-white hover:text-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                    aria-label="Search products">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                    </svg>
                </button>

                <div class="relative hidden sm:block">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                    </svg>
                    <input
                        x-model="query"
                        @input="onInput"
                        @focus="if (query.trim()) open = true"
                        @keydown.enter.prevent="submitSearch"
                        type="search"
                        placeholder="Search digital products..."
                        class="w-full h-9 pl-9 pr-3 rounded-lg border border-slate-200 bg-white/90 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 shadow-sm outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100" />
                </div>

                <template x-teleport="body">
                    <div
                        x-cloak
                        x-show="mobileSearchOpen"
                        class="fixed inset-0 z-[100] sm:hidden"
                        role="dialog"
                        aria-modal="true"
                        aria-label="Product search">
                        <div class="absolute inset-0 bg-slate-900/45 backdrop-blur-[2px]" @click="mobileSearchOpen = false; open = false"></div>
                        <div
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="translate-y-3 opacity-0"
                            x-transition:enter-end="translate-y-0 opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="translate-y-0 opacity-100"
                            x-transition:leave-end="translate-y-3 opacity-0"
                            class="absolute top-4 rounded-2xl border border-white/70 bg-white shadow-2xl"
                            style="left: 50%; width: min(calc(100vw - 24px), 360px); transform: translateX(-50%);">
                            <div class="flex items-center gap-2 border-b border-slate-100 px-3 py-3">
                                <div class="relative min-w-0 flex-1">
                                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                    </svg>
                                    <input
                                        x-ref="mobileSearchInput"
                                        x-model="query"
                                        @input="onInput"
                                        @keydown.enter.prevent="submitSearch"
                                        type="search"
                                        placeholder="Search digital products..."
                                        class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-2 focus:ring-indigo-100" />
                                </div>
                                <button
                                    type="button"
                                    @click="mobileSearchOpen = false; open = false"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                                    aria-label="Close search">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="max-h-[70vh] overflow-y-auto">
                                <template x-if="loading">
                                    <div class="px-4 py-4 text-sm text-slate-500">Searching...</div>
                                </template>

                                <template x-if="!loading && query.trim().length > 0 && products.length === 0">
                                    <div class="px-4 py-4 text-sm text-slate-500">No products found.</div>
                                </template>

                                <template x-if="!loading && query.trim().length === 0">
                                    <div class="px-4 py-4 text-sm text-slate-500">Type a product name to search.</div>
                                </template>

                                <ul x-show="!loading && products.length > 0" class="divide-y divide-slate-100">
                                    <template x-for="product in products" :key="product.slug">
                                        <li>
                                            <a :href="product.url" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition">
                                                <template x-if="product.image">
                                                    <img :src="product.image" alt="" class="h-11 w-11 rounded-xl object-cover border border-slate-200" width="44" height="44" loading="lazy" decoding="async" />
                                                </template>
                                                <template x-if="!product.image">
                                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-500 border border-slate-200">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Zm4 3h10m-10 4h6" />
                                                        </svg>
                                                    </span>
                                                </template>
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-semibold text-slate-800" x-text="product.title"></p>
                                                    <p class="truncate text-xs text-slate-500" x-text="product.category || 'Product'"></p>
                                                </div>
                                                <span class="text-xs font-semibold text-indigo-600" x-text="`৳${Number(product.price).toFixed(2)}`"></span>
                                            </a>
                                        </li>
                                    </template>
                                </ul>

                                <button
                                    x-show="query.trim().length > 0"
                                    @click="submitSearch"
                                    class="w-full border-t border-slate-100 px-4 py-3 text-left text-xs font-semibold tracking-wide text-indigo-600 hover:bg-indigo-50 transition">
                                    View all results
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-cloak x-show="open" class="hidden sm:block sm:absolute sm:left-0 sm:right-0 sm:top-[calc(100%+8px)] sm:z-[95]">
                    <div class="rounded-2xl border border-slate-200 bg-white/95 backdrop-blur-xl shadow-[0_18px_45px_rgba(15,23,42,0.18)] overflow-hidden">
                        <template x-if="loading">
                            <div class="px-4 py-3 text-sm text-slate-500">Searching...</div>
                        </template>

                        <template x-if="!loading && products.length === 0">
                            <div class="px-4 py-3 text-sm text-slate-500">No products found.</div>
                        </template>

                        <ul x-show="!loading && products.length > 0" class="max-h-[350px] overflow-y-auto divide-y divide-slate-100">
                            <template x-for="product in products" :key="product.slug">
                                <li>
                                    <a :href="product.url" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition" @click="open = false">
                                        <template x-if="product.image">
                                            <img :src="product.image" alt="" class="h-10 w-10 rounded-lg object-cover border border-slate-200" width="40" height="40" loading="lazy" decoding="async" />
                                        </template>
                                        <template x-if="!product.image">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-500 border border-slate-200">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Zm4 3h10m-10 4h6" />
                                                </svg>
                                            </span>
                                        </template>

                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-slate-800" x-text="product.title"></p>
                                            <p class="truncate text-xs text-slate-500" x-text="product.category || 'Product'"></p>
                                        </div>
                                        <span class="text-xs font-semibold text-indigo-600" x-text="`৳${Number(product.price).toFixed(2)}`"></span>
                                    </a>
                                </li>
                            </template>
                        </ul>

                        <button
                            x-show="query.trim().length > 0"
                            @click="submitSearch"
                            class="w-full border-t border-slate-100 px-4 py-2.5 text-left text-xs font-semibold tracking-wide text-indigo-600 hover:bg-indigo-50 transition">
                            View all results
                        </button>
                    </div>
                </div>
            </div>

            <div class="site-header-actions flex items-center justify-end gap-0.5 sm:gap-2">
                <div x-data="{ open: false }" class="relative hidden md:block">
                    <button type="button" @click="open = !open" class="inline-flex items-center gap-2 h-9 px-3 rounded-xl bg-white/70 border border-gray-200 text-sm font-semibold text-gray-900 hover:bg-white hover:border-gray-300 shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2" aria-label="Currency">
                        <span class="text-[12px] font-bold tracking-wide"><?php echo e($currencyCode); ?></span>
                        <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div x-cloak x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-lg ring-1 ring-black/5 overflow-hidden">
                        <a href="<?php echo e(route('currency.switch', ['currency' => 'BDT'])); ?>" class="block px-4 py-2 text-sm <?php echo e($currencyCode === 'BDT' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'); ?>"><?php echo e(__('ui.currency_bdt')); ?></a>
                        <a href="<?php echo e(route('currency.switch', ['currency' => 'USD'])); ?>" class="block px-4 py-2 text-sm <?php echo e($currencyCode === 'USD' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'); ?>"><?php echo e(__('ui.currency_usd')); ?></a>
                        <a href="<?php echo e(route('currency.switch', ['currency' => 'RUB'])); ?>" class="block px-4 py-2 text-sm <?php echo e($currencyCode === 'RUB' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'); ?>"><?php echo e(__('ui.currency_rub')); ?></a>
                    </div>
                </div>

                <div x-data="{ open: false }" class="relative hidden md:block">
                    <button type="button" @click="open = !open" class="inline-flex items-center gap-2 h-9 px-3 rounded-xl bg-white/70 border border-gray-200 text-sm font-semibold text-gray-900 hover:bg-white hover:border-gray-300 shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2" aria-label="Language">
                        <span class="text-[12px] font-bold tracking-wide"><?php echo e($langLabel); ?></span>
                        <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div x-cloak x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-lg ring-1 ring-black/5 overflow-hidden">
                        <a href="<?php echo e(route('lang.switch', ['locale' => 'en'])); ?>" class="block px-4 py-2 text-sm <?php echo e($locale === 'en' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'); ?>"><?php echo e(__('ui.english')); ?></a>
                        <a href="<?php echo e(route('lang.switch', ['locale' => 'bn'])); ?>" class="block px-4 py-2 text-sm <?php echo e($locale === 'bn' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'); ?>"><?php echo e(__('ui.bangla')); ?></a>
                        <a href="<?php echo e(route('lang.switch', ['locale' => 'ru'])); ?>" class="block px-4 py-2 text-sm <?php echo e($locale === 'ru' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'); ?>"><?php echo e(__('ui.russian')); ?></a>
                    </div>
                </div>

                <?php if(auth()->guard()->check()): ?>
                    <div x-data="{ open: false }" class="site-header-mobile-notification relative">
                        <button
                            type="button"
                            @click="open = !open"
                            class="site-header-icon-button relative inline-flex h-9 w-9 items-center justify-center rounded-md text-gray-700 hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                            aria-label="Open notifications"
                            :aria-expanded="open.toString()">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 17h5l-1.4-1.4A2 2 0 0 1 17 14.2V11a5 5 0 0 0-3-4.6V5a2 2 0 1 0-4 0v1.4A5 5 0 0 0 7 11v3.2c0 .5-.2 1-.6 1.4L5 17h5m4 0a2 2 0 1 1-4 0m4 0h-4" />
                            </svg>
                            <?php if($userUnreadNotifications > 0): ?>
                                <span class="absolute -top-1 -right-1 inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold leading-none text-white ring-2 ring-white">
                                    <?php echo e($userUnreadNotifications > 99 ? '99+' : $userUnreadNotifications); ?>

                                </span>
                            <?php endif; ?>
                        </button>

                        <div
                            x-cloak
                            x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="translate-y-1 opacity-0"
                            x-transition:enter-end="translate-y-0 opacity-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="translate-y-0 opacity-100"
                            x-transition:leave-end="translate-y-1 opacity-0"
                            @click.outside="open = false"
                            class="site-header-notification-panel absolute right-0 mt-2 overflow-hidden rounded-lg bg-white shadow-xl ring-1 ring-black/5"
                            style="width: min(370px, calc(100vw - 1.5rem)); max-height: calc(100vh - 6rem);">
                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notifications</div>
                                <span class="rounded bg-indigo-50 px-2 py-1 text-[11px] font-semibold uppercase text-indigo-700"><?php echo e($userUnreadNotifications); ?> unread</span>
                            </div>

                            <div class="overflow-y-auto" style="max-height: min(430px, calc(100vh - 180px));">
                                <?php $__empty_1 = true; $__currentLoopData = $userRecentNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $notificationUnread = $notification->read_at === null;
                                        $notificationColor = match ($notification->severity) {
                                            'danger' => 'bg-red-500',
                                            'warning' => 'bg-amber-500',
                                            'success' => 'bg-emerald-500',
                                            default => 'bg-indigo-500',
                                        };
                                    ?>
                                    <a href="<?php echo e(route('notifications.open', $notification)); ?>" class="flex gap-3 border-b border-slate-100 px-4 py-3 transition hover:bg-slate-50 <?php echo e($notificationUnread ? 'bg-indigo-50/50' : ''); ?>">
                                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full <?php echo e($notificationUnread ? $notificationColor : 'bg-slate-300'); ?>"></span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-semibold text-slate-900"><?php echo e($notification->title); ?></span>
                                            <?php if($notification->body): ?>
                                                <span class="mt-0.5 block truncate text-xs text-slate-500"><?php echo e($notification->body); ?></span>
                                            <?php endif; ?>
                                            <span class="mt-1 block text-[11px] font-medium text-slate-400"><?php echo e($notification->created_at?->diffForHumans()); ?></span>
                                        </span>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="px-4 py-6 text-sm text-slate-500">No notifications yet.</div>
                                <?php endif; ?>
                            </div>

                            <a href="<?php echo e(route('notifications.index')); ?>" class="block border-t border-slate-100 px-4 py-3 text-center text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50">
                                View all notifications
                            </a>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="site-header-mobile-profile relative">
                        <button type="button" @click="open = !open" class="site-header-icon-button inline-flex items-center justify-center h-9 w-9 rounded-md text-gray-700 hover:bg-gray-100" aria-label="Profile">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>

                        <div x-cloak x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 rounded-md bg-white shadow-lg ring-1 ring-black/5 overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="text-sm font-medium text-gray-900"><?php echo e(Auth::user()->name); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e(Auth::user()->email); ?></div>
                            </div>

                            <a href="<?php echo e(route('dashboard')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Dashboard</a>
                            <a href="<?php echo e(route('downloads.index')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Download Center</a>
                            <a href="<?php echo e(route('tickets.index')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Support Tickets</a>

                            <?php if(Auth::user()->is_admin ?? false): ?>
                                <a href="<?php echo e(route('admin.dashboard')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><?php echo e(__('ui.admin')); ?></a>
                            <?php endif; ?>

                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><?php echo e(__('ui.logout')); ?></button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-2">
                        <a href="<?php echo e(route('login')); ?>" class="site-header-login-link inline-flex items-center justify-center h-9 px-4 rounded-xl bg-white/70 border border-gray-200 text-sm font-semibold text-gray-900 hover:bg-white hover:border-gray-300 shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"><?php echo e(__('ui.login')); ?></a>
                    </div>
                <?php endif; ?>

                <button
                    type="button"
                    @click="cartOpen = true"
                    class="site-header-icon-button site-header-cart-button relative inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white/75 text-slate-700 shadow-sm transition hover:bg-white hover:text-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                    aria-label="Open cart">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h15l-1.5 9h-12L6 6Zm0 0 0-2H3m6 16a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                    </svg>
                    <?php if($cartCount > 0): ?>
                        <span class="site-header-cart-badge absolute inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-indigo-600 px-1 text-[10px] font-semibold leading-none text-white ring-2 ring-white">
                            <?php echo e($cartBadge); ?>

                        </span>
                    <?php endif; ?>
                </button>
            </div>
        </div>
    </div>

    <button type="button"
        @click="menuOpen = true"
        class="absolute left-2 top-2 sm:left-3 sm:top-2.5 md:left-4 inline-flex items-center justify-center h-10 w-10 rounded-xl bg-gradient-to-b from-white to-slate-100/95 border border-white/80 text-slate-900 shadow-[0_8px_22px_rgba(15,23,42,0.18)] ring-1 ring-slate-200/80 transition hover:from-white hover:to-white hover:shadow-[0_10px_24px_rgba(15,23,42,0.24)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
        aria-label="Open menu"
        :aria-expanded="menuOpen.toString()"
        aria-controls="site-drawer-menu">
        <span class="relative inline-flex h-5 w-5">
            <span class="absolute left-0 top-0 block h-[2px] w-5 origin-center rounded-full bg-slate-900 transition duration-300" :class="menuOpen ? 'translate-y-2 rotate-45' : ''"></span>
            <span class="absolute left-0 top-2 block h-[2px] w-5 origin-center rounded-full bg-slate-900 transition duration-300" :class="menuOpen ? 'opacity-0' : ''"></span>
            <span class="absolute left-0 top-4 block h-[2px] w-5 origin-center rounded-full bg-slate-900 transition duration-300" :class="menuOpen ? '-translate-y-2 -rotate-45' : ''"></span>
        </span>
    </button>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="cartOpen"
            class="fixed inset-0 z-[95]"
            role="dialog"
            aria-modal="true"
            aria-label="Cart drawer">
            <div
                class="absolute inset-0 bg-slate-950/45"
                style="-webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);"
                @click="cartOpen = false"></div>

            <aside
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-full opacity-0"
                class="absolute right-0 top-0 h-dvh w-[min(94vw,420px)] bg-white shadow-2xl">
                <div class="flex h-full flex-col">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-500">Shopping Cart</p>
                            <h2 class="mt-1 text-base font-semibold text-slate-950"><?php echo e($cartCount); ?> <?php echo e($cartCount === 1 ? 'item' : 'items'); ?></h2>
                        </div>
                        <button
                            type="button"
                            @click="cartOpen = false"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-950"
                            aria-label="Close cart">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto px-5 py-4">
                        <?php if(count($cartDrawerItems) === 0): ?>
                            <div class="flex min-h-[360px] flex-col items-center justify-center text-center">
                                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6h15l-1.5 9h-12L6 6Zm0 0 0-2H3m6 16a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                                    </svg>
                                </span>
                                <p class="mt-4 text-sm font-semibold text-slate-950"><?php echo e(__('ui.cart_empty')); ?></p>
                                <p class="mt-1 max-w-xs text-sm text-slate-500">Browse products and add your digital items here.</p>
                                <a href="<?php echo e(route('products.index')); ?>" @click="cartOpen = false" class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                    <?php echo e(__('ui.browse_products')); ?>

                                </a>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $cartDrawerItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $product = $item['product'];
                                        $meta = is_array($item['meta'] ?? null) ? ($item['meta'] ?? []) : [];
                                        $optionLabels = is_array($meta['option_labels'] ?? null) ? $meta['option_labels'] : [];
                                        $rawOptions = is_array($meta['options'] ?? null) ? $meta['options'] : [];
                                        $displayOptions = count($optionLabels) > 0 ? $optionLabels : $rawOptions;
                                        $displayOptions = is_array($displayOptions) ? $displayOptions : [];
                                    ?>
                                    <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                                        <div class="flex gap-3">
                                            <a href="<?php echo e(route('products.show', $product)); ?>" @click="cartOpen = false" class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                                                <?php if(!empty($product->image)): ?>
                                                    <img src="<?php echo e(Storage::url($product->image)); ?>" alt="<?php echo e($product->title); ?>" class="h-full w-full object-cover" width="56" height="56" loading="lazy" decoding="async">
                                                <?php else: ?>
                                                    <span class="flex h-full w-full items-center justify-center text-slate-400">
                                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Zm4 3h8m-8 4h5" />
                                                        </svg>
                                                    </span>
                                                <?php endif; ?>
                                            </a>

                                            <div class="min-w-0 flex-1">
                                                <div class="truncate text-xs font-medium text-slate-500"><?php echo e($product->category?->name ?? 'Product'); ?></div>
                                                <a href="<?php echo e(route('products.show', $product)); ?>" @click="cartOpen = false" class="mt-0.5 block truncate text-sm font-semibold text-slate-950 hover:text-indigo-600"><?php echo e($product->title); ?></a>
                                                <div class="mt-1 text-sm font-semibold text-slate-900"><?php if (isset($component)) { $__componentOriginal3c51c5b308d311657bfae6be692a1470 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c51c5b308d311657bfae6be692a1470 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.money','data' => ['amount' => (float) $item['unit_price']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('money'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((float) $item['unit_price'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $attributes = $__attributesOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__attributesOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $component = $__componentOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__componentOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?></div>

                                                <?php if(!empty($displayOptions)): ?>
                                                    <div class="mt-2 flex flex-wrap gap-1">
                                                        <?php $__currentLoopData = $displayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-700 ring-1 ring-indigo-100">
                                                                <?php echo e($label); ?>: <?php echo e($value); ?>

                                                            </span>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex items-center justify-between gap-3">
                                            <form method="POST" action="<?php echo e(route('cart.update', $product)); ?>" class="flex items-center rounded-xl border border-slate-200">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="cart_key" value="<?php echo e($item['cart_key'] ?? $product->id); ?>">
                                                <button type="submit" name="quantity" value="<?php echo e(max(1, (int) $item['quantity'] - 1)); ?>" class="h-9 w-9 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" aria-label="Decrease quantity">-</button>
                                                <span class="flex h-9 min-w-10 items-center justify-center border-x border-slate-200 px-3 text-sm font-semibold text-slate-900"><?php echo e($item['quantity']); ?></span>
                                                <button type="submit" name="quantity" value="<?php echo e(min(99, (int) $item['quantity'] + 1)); ?>" class="h-9 w-9 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" aria-label="Increase quantity">+</button>
                                            </form>

                                            <div class="flex items-center gap-3">
                                                <div class="text-sm font-semibold text-slate-950"><?php if (isset($component)) { $__componentOriginal3c51c5b308d311657bfae6be692a1470 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c51c5b308d311657bfae6be692a1470 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.money','data' => ['amount' => (float) $item['subtotal']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('money'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((float) $item['subtotal'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $attributes = $__attributesOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__attributesOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $component = $__componentOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__componentOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?></div>
                                                <form method="POST" action="<?php echo e(route('cart.remove', $product)); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="cart_key" value="<?php echo e($item['cart_key'] ?? $product->id); ?>">
                                                    <button class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600" aria-label="<?php echo e(__('ui.remove')); ?>">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-9 0V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-.7 12a2 2 0 0 1-2 1.9H9.7a2 2 0 0 1-2-1.9L7 7" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="border-t border-slate-100 bg-white px-5 py-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500"><?php echo e(__('ui.total')); ?></span>
                            <span class="text-lg font-bold text-slate-950"><?php if (isset($component)) { $__componentOriginal3c51c5b308d311657bfae6be692a1470 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c51c5b308d311657bfae6be692a1470 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.money','data' => ['amount' => (float) $cartDrawerTotal]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('money'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((float) $cartDrawerTotal)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $attributes = $__attributesOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__attributesOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $component = $__componentOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__componentOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?></span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <a href="<?php echo e(route('cart')); ?>" @click="cartOpen = false" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-800 transition hover:bg-slate-50">
                                <?php echo e(__('ui.cart')); ?>

                            </a>
                            <a href="<?php echo e(route('checkout')); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'inline-flex h-11 items-center justify-center rounded-xl px-3 text-sm font-semibold text-white transition',
                                'bg-indigo-600 hover:bg-indigo-700' => count($cartDrawerItems) > 0,
                                'pointer-events-none bg-slate-300' => count($cartDrawerItems) === 0,
                            ]); ?>">
                                <?php echo e(__('ui.proceed_to_checkout')); ?>

                            </a>
                        </div>

                        <?php if(count($cartDrawerItems) > 0): ?>
                            <form method="POST" action="<?php echo e(route('cart.clear')); ?>" class="mt-2">
                                <?php echo csrf_field(); ?>
                                <button class="h-10 w-full rounded-xl text-sm font-semibold text-slate-500 transition hover:bg-slate-50 hover:text-red-600">
                                    <?php echo e(__('ui.clear_cart')); ?>

                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
        </div>
    </template>

    <template x-teleport="body">
        <div id="site-drawer-menu"
            x-cloak
            x-show="menuOpen"
            class="fixed inset-0 z-[90]"
            role="dialog"
            aria-modal="true"
            aria-label="Site menu">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]" @click="menuOpen = false"></div>

            <aside
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                class="absolute left-0 top-0 h-dvh w-[min(92vw,380px)] bg-white/95 backdrop-blur-xl border-r border-white/70 shadow-2xl">
                <div class="flex h-full flex-col">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200/70 bg-gradient-to-r from-indigo-50 via-white to-white">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-indigo-500">Navigation</p>
                        <p class="text-base font-semibold text-gray-900"><?php echo e(config('app.name')); ?></p>
                    </div>
                    <button type="button"
                        @click="menuOpen = false"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"
                        aria-label="Close menu">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
                    <nav class="space-y-2">
                        <a href="<?php echo e(route('home')); ?>" @click="menuOpen = false" class="group flex items-center gap-3 rounded-xl border px-3 py-3 text-sm font-medium transition <?php echo e(request()->routeIs('home') ? 'border-indigo-100 bg-indigo-50 text-indigo-700' : 'border-transparent text-gray-700 hover:border-gray-200 hover:bg-gray-50'); ?>">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg <?php echo e(request()->routeIs('home') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'); ?>">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l9-7 9 7v10a2 2 0 01-2 2h-4v-6H9v6H5a2 2 0 01-2-2V10z" /></svg>
                            </span>
                            <span><?php echo e(__('ui.home')); ?></span>
                        </a>

                        <a href="<?php echo e(route('categories')); ?>" @click="menuOpen = false" class="group flex items-center gap-3 rounded-xl border px-3 py-3 text-sm font-medium transition <?php echo e(request()->routeIs('categories') ? 'border-indigo-100 bg-indigo-50 text-indigo-700' : 'border-transparent text-gray-700 hover:border-gray-200 hover:bg-gray-50'); ?>">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg <?php echo e(request()->routeIs('categories') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'); ?>">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm0 9a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3zm9-9a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2V6zm0 9a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2v-3z" /></svg>
                            </span>
                            <span><?php echo e(__('ui.categories')); ?></span>
                        </a>

                        <a href="<?php echo e(route('products.index')); ?>" @click="menuOpen = false" class="group flex items-center gap-3 rounded-xl border px-3 py-3 text-sm font-medium transition <?php echo e(request()->routeIs('products.*') ? 'border-indigo-100 bg-indigo-50 text-indigo-700' : 'border-transparent text-gray-700 hover:border-gray-200 hover:bg-gray-50'); ?>">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg <?php echo e(request()->routeIs('products.*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'); ?>">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0l-2 8H8l-2-8m16 0H4" /></svg>
                            </span>
                            <span><?php echo e(__('ui.products')); ?></span>
                        </a>

                        <a href="<?php echo e(route('cart')); ?>" @click="menuOpen = false" class="group flex items-center gap-3 rounded-xl border px-3 py-3 text-sm font-medium transition <?php echo e(request()->routeIs('cart') ? 'border-indigo-100 bg-indigo-50 text-indigo-700' : 'border-transparent text-gray-700 hover:border-gray-200 hover:bg-gray-50'); ?>">
                            <span class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg <?php echo e(request()->routeIs('cart') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'); ?>">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437m0 0L7.5 14.25a3 3 0 0 0 3 3h8.25m-11.25-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84m0 0L5.106 5.272M6 20.25a.75.75 0 1 0-1.5 0 .75.75 0 0 0 1.5 0Zm12.75 0a.75.75 0 1 0-1.5 0 .75.75 0 0 0 1.5 0Z" />
                                </svg>
                                <?php if($cartCount > 0): ?>
                                    <span class="absolute -top-1 -right-1 inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-indigo-600 px-1 text-[10px] font-semibold leading-none text-white ring-2 ring-white">
                                        <?php echo e($cartBadge); ?>

                                    </span>
                                <?php endif; ?>
                            </span>
                            <span><?php echo e(__('ui.cart')); ?></span>
                        </a>

                        <a href="<?php echo e(route('orders')); ?>" @click="menuOpen = false" class="group flex items-center gap-3 rounded-xl border px-3 py-3 text-sm font-medium transition <?php echo e(request()->routeIs('orders*') ? 'border-indigo-100 bg-indigo-50 text-indigo-700' : 'border-transparent text-gray-700 hover:border-gray-200 hover:bg-gray-50'); ?>">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg <?php echo e(request()->routeIs('orders*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'); ?>">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7a1 1 0 011-1h8a1 1 0 011 1v10M9 17H7a1 1 0 01-1-1V7a1 1 0 011-1h2m0 11h10m-5 4a2 2 0 01-2-2h4a2 2 0 01-2 2z" /></svg>
                            </span>
                            <span><?php echo e(__('ui.orders')); ?></span>
                        </a>

                        <?php if(auth()->guard()->check()): ?>
                            <a href="<?php echo e(route('notifications.index')); ?>" @click="menuOpen = false" class="group flex items-center gap-3 rounded-xl border px-3 py-3 text-sm font-medium transition <?php echo e(request()->routeIs('notifications.*') ? 'border-indigo-100 bg-indigo-50 text-indigo-700' : 'border-transparent text-gray-700 hover:border-gray-200 hover:bg-gray-50'); ?>">
                                <span class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg <?php echo e(request()->routeIs('notifications.*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'); ?>">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 17h5l-1.4-1.4A2 2 0 0 1 17 14.2V11a5 5 0 0 0-3-4.6V5a2 2 0 1 0-4 0v1.4A5 5 0 0 0 7 11v3.2c0 .5-.2 1-.6 1.4L5 17h5m4 0a2 2 0 1 1-4 0m4 0h-4" />
                                    </svg>
                                    <?php if($userUnreadNotifications > 0): ?>
                                        <span class="absolute -top-1 -right-1 inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold leading-none text-white ring-2 ring-white">
                                            <?php echo e($userUnreadNotifications > 99 ? '99+' : $userUnreadNotifications); ?>

                                        </span>
                                    <?php endif; ?>
                                </span>
                                <span>Notifications</span>
                            </a>

                            <a href="<?php echo e(route('downloads.index')); ?>" @click="menuOpen = false" class="group flex items-center gap-3 rounded-xl border px-3 py-3 text-sm font-medium transition <?php echo e(request()->routeIs('downloads.*') ? 'border-indigo-100 bg-indigo-50 text-indigo-700' : 'border-transparent text-gray-700 hover:border-gray-200 hover:bg-gray-50'); ?>">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg <?php echo e(request()->routeIs('downloads.*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'); ?>">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v10m0 0l-4-4m4 4l4-4" />
                                    </svg>
                                </span>
                                <span>Downloads</span>
                            </a>

                            <a href="<?php echo e(route('tickets.index')); ?>" @click="menuOpen = false" class="group flex items-center gap-3 rounded-xl border px-3 py-3 text-sm font-medium transition <?php echo e(request()->routeIs('tickets.*') ? 'border-indigo-100 bg-indigo-50 text-indigo-700' : 'border-transparent text-gray-700 hover:border-gray-200 hover:bg-gray-50'); ?>">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg <?php echo e(request()->routeIs('tickets.*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'); ?>">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8m-8 4h5m-8 7h10a2 2 0 0 0 2-2V8l-4-4H7a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z" />
                                    </svg>
                                </span>
                                <span>Tickets</span>
                            </a>
                        <?php endif; ?>
                    </nav>

                </div>

                <div class="border-t border-gray-200/80 px-4 py-4 bg-white/90">
                    <?php if(auth()->guard()->check()): ?>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <p class="truncate text-sm font-semibold text-gray-900"><?php echo e(Auth::user()->name); ?></p>
                            <p class="truncate text-xs text-gray-500"><?php echo e(Auth::user()->email); ?></p>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <a href="<?php echo e(route('dashboard')); ?>" @click="menuOpen = false" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-3 py-2.5 text-xs font-semibold text-white transition hover:bg-indigo-700">Dashboard</a>
                                <form method="POST" action="<?php echo e(route('logout')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">Logout</button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-600 to-violet-600 p-4 text-white shadow-sm">
                            <p class="text-sm font-semibold">Welcome to <?php echo e(config('app.name')); ?></p>
                            <p class="mt-1 text-xs text-white/75">Sign in to manage orders, downloads and support tickets.</p>
                            <div class="mt-3">
                                <a href="<?php echo e(route('login')); ?>" @click="menuOpen = false" class="inline-flex w-full items-center justify-center rounded-xl bg-white px-3 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50"><?php echo e(__('ui.login')); ?></a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                </div>
            </aside>
        </div>
    </template>
</header>
<?php /**PATH C:\xampp\htdocs\digify\resources\views/layouts/partials/site-header.blade.php ENDPATH**/ ?>