<?php
    $hide = request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.*');
    $cart = session('cart', []);
    $cartCount = 0;
    if (is_array($cart)) {
        foreach ($cart as $row) {
            if (is_array($row)) {
                $cartCount += (int) ($row['quantity'] ?? 0);
            } else {
                $cartCount += (int) $row;
            }
        }
    }
    $cartBadge = $cartCount > 99 ? '99+' : (string) $cartCount;
?>

<?php if(!$hide): ?>
    <nav class="sm:hidden fixed bottom-0 inset-x-0 z-50 pb-[env(safe-area-inset-bottom)]">
        <div class="mx-3 mb-3 rounded-2xl bg-white/70 backdrop-blur border border-white/40 shadow-lg">
            <div class="flex items-center justify-between py-2">
                <a href="<?php echo e(route('home')); ?>" class="flex flex-1 flex-col items-center justify-center gap-1 py-1 <?php echo e(request()->routeIs('home') ? 'text-indigo-600' : 'text-gray-600'); ?>">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 4l9 5.75V20a1 1 0 01-1 1h-5v-7H9v7H4a1 1 0 01-1-1V9.75z" />
                    </svg>
                    <span class="text-[11px] font-medium"><?php echo e(__('ui.home')); ?></span>
                </a>

                <a href="<?php echo e(route('categories')); ?>" class="flex flex-1 flex-col items-center justify-center gap-1 py-1 <?php echo e(request()->routeIs('categories') ? 'text-indigo-600' : 'text-gray-600'); ?>">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm0 9a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3zm9-9a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2V6zm0 9a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2v-3z" />
                    </svg>
                    <span class="text-[11px] font-medium"><?php echo e(__('ui.categories')); ?></span>
                </a>

                <a href="<?php echo e(route('products.index')); ?>" class="flex flex-1 flex-col items-center justify-center gap-1 py-1 <?php echo e(request()->routeIs('products.*') ? 'text-indigo-600' : 'text-gray-600'); ?>">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0l-2 8H8l-2-8m16 0H4" />
                    </svg>
                    <span class="text-[11px] font-medium"><?php echo e(__('ui.products')); ?></span>
                </a>

                <a href="<?php echo e(route('orders')); ?>" class="flex flex-1 flex-col items-center justify-center gap-1 py-1 <?php echo e(request()->routeIs('orders') ? 'text-indigo-600' : 'text-gray-600'); ?>">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7a1 1 0 011-1h8a1 1 0 011 1v10M9 17H7a1 1 0 01-1-1V7a1 1 0 011-1h2m0 11h10m-5 4a2 2 0 01-2-2h4a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-[11px] font-medium"><?php echo e(__('ui.orders')); ?></span>
                </a>

                <a href="<?php echo e(route('cart')); ?>" class="flex flex-1 flex-col items-center justify-center gap-1 py-1 <?php echo e(request()->routeIs('cart') ? 'text-indigo-600' : 'text-gray-600'); ?>">
                    <span class="relative inline-flex">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437m0 0L7.5 14.25a3 3 0 0 0 3 3h8.25m-11.25-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84m0 0L5.106 5.272M6 20.25a.75.75 0 1 0-1.5 0 .75.75 0 0 0 1.5 0Zm12.75 0a.75.75 0 1 0-1.5 0 .75.75 0 0 0 1.5 0Z" />
                        </svg>

                        <?php if($cartCount > 0): ?>
                            <span class="absolute -top-1 -right-2 inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-indigo-600 px-1 text-[10px] font-semibold leading-none text-white ring-2 ring-white">
                                <?php echo e($cartBadge); ?>

                            </span>
                        <?php endif; ?>
                    </span>
                    <span class="text-[11px] font-medium"><?php echo e(__('ui.cart')); ?></span>
                </a>

                <a href="<?php echo e(auth()->check() ? route('dashboard') : route('login')); ?>" class="flex flex-1 flex-col items-center justify-center gap-1 py-1 <?php echo e(request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-600'); ?>">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5.5A1.5 1.5 0 015.5 4h5A1.5 1.5 0 0112 5.5v5a1.5 1.5 0 01-1.5 1.5h-5A1.5 1.5 0 014 10.5v-5Zm8 8A1.5 1.5 0 0113.5 12h5a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 01-1.5 1.5h-5a1.5 1.5 0 01-1.5-1.5v-5ZM14 4h4a2 2 0 012 2v3m-8 11H7a3 3 0 01-3-3v-3" />
                    </svg>
                    <span class="text-[11px] font-medium">Dashboard</span>
                </a>
            </div>
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\digify\resources\views/layouts/partials/mobile-bottom-nav.blade.php ENDPATH**/ ?>