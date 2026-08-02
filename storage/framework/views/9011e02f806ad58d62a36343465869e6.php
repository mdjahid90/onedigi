<?php if (isset($component)) { $__componentOriginal333b9e857c198bd0078774586fa40930 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal333b9e857c198bd0078774586fa40930 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-dashboard-layout','data' => ['title' => 'Dashboard','pretitle' => 'User Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('user-dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','pretitle' => 'User Dashboard']); ?>
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v10M9 17H7a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h2m0 11h10m-5 4a2 2 0 0 1-2-2h4a2 2 0 0 1-2 2z" />
                    </svg>
                </div>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">Total Orders</p>
                <p class="mt-2 text-3xl font-bold text-slate-900"><?php echo e($totalOrders); ?></p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                    </svg>
                </div>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">Active Orders</p>
                <p class="mt-2 text-3xl font-bold text-slate-900"><?php echo e($activeOrders); ?></p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">Delivered</p>
                <p class="mt-2 text-3xl font-bold text-slate-900"><?php echo e($deliveredOrders); ?></p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 .895-4 2s1.79 2 4 2 4 .895 4 2-1.79 2-4 2m0-10V6m0 12v-2" />
                    </svg>
                </div>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">Total Spent</p>
                <p class="mt-2 text-2xl font-bold text-slate-900"><?php if (isset($component)) { $__componentOriginal3c51c5b308d311657bfae6be692a1470 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c51c5b308d311657bfae6be692a1470 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.money','data' => ['amount' => $totalSpent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('money'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalSpent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $attributes = $__attributesOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__attributesOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $component = $__componentOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__componentOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Recent Orders</h2>
                    <a href="<?php echo e(route('orders')); ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">View all</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium">Order</th>
                                <th class="px-5 py-3 text-left font-medium">Status</th>
                                <th class="px-5 py-3 text-left font-medium">Amount</th>
                                <th class="px-5 py-3 text-left font-medium">Date</th>
                                <th class="px-5 py-3 text-right font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-5 py-3 font-semibold text-slate-900">#<?php echo e($order->id); ?></td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($order->status === 'DELIVERED' ? 'bg-emerald-50 text-emerald-700' : ($order->status === 'CANCELLED' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700')); ?>">
                                            <?php echo e($order->status); ?>

                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-700"><?php if (isset($component)) { $__componentOriginal3c51c5b308d311657bfae6be692a1470 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c51c5b308d311657bfae6be692a1470 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.money','data' => ['amount' => (float) $order->total_amount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('money'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((float) $order->total_amount)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $attributes = $__attributesOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__attributesOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $component = $__componentOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__componentOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?></td>
                                    <td class="px-5 py-3 text-slate-600"><?php echo e($order->created_at?->format('Y-m-d H:i')); ?></td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="<?php echo e(route('orders.show', $order)); ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td class="px-5 py-10 text-center" colspan="5">
                                        <div class="mx-auto max-w-sm rounded-xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8">
                                            <p class="text-sm font-semibold text-slate-700">No orders yet</p>
                                            <p class="mt-1 text-xs text-slate-500">Start shopping to see your order activity here.</p>
                                            <a href="<?php echo e(route('products.index')); ?>" class="mt-4 inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-black">Browse Products</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900">Downloads</h2>
                    <p class="mt-2 text-3xl font-bold text-slate-900"><?php echo e($downloadableOrders); ?></p>
                    <p class="mt-2 text-sm text-slate-600">Delivered orders with downloadable file or link.</p>
                    <a href="<?php echo e(route('downloads.index')); ?>" class="mt-4 inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-black">Go to Download Center</a>

                    <?php if($latestDownloadOrder && $latestDownloadOrder->delivery?->file_path): ?>
                        <a href="<?php echo e(route('orders.delivery.download', $latestDownloadOrder)); ?>" class="mt-2 inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">One-click latest file</a>
                    <?php elseif($latestDownloadOrder && $latestDownloadOrder->delivery?->delivery_link): ?>
                        <a href="<?php echo e($latestDownloadOrder->delivery->delivery_link); ?>" target="_blank" rel="noreferrer" class="mt-2 inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Open latest delivery link</a>
                    <?php endif; ?>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900">Quick Actions</h2>
                    <div class="mt-4 space-y-2">
                        <a href="<?php echo e(route('tickets.index')); ?>" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"><span>Support Tickets</span><span aria-hidden="true">-></span></a>
                        <a href="<?php echo e(route('downloads.index')); ?>" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"><span>Open Download Center</span><span aria-hidden="true">-></span></a>
                        <a href="<?php echo e(route('products.index')); ?>" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"><span>Browse Products</span><span aria-hidden="true">-></span></a>
                        <a href="<?php echo e(route('cart')); ?>" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"><span>Open Cart</span><span aria-hidden="true">-></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal333b9e857c198bd0078774586fa40930)): ?>
<?php $attributes = $__attributesOriginal333b9e857c198bd0078774586fa40930; ?>
<?php unset($__attributesOriginal333b9e857c198bd0078774586fa40930); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal333b9e857c198bd0078774586fa40930)): ?>
<?php $component = $__componentOriginal333b9e857c198bd0078774586fa40930; ?>
<?php unset($__componentOriginal333b9e857c198bd0078774586fa40930); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\digify\resources\views/dashboard.blade.php ENDPATH**/ ?>