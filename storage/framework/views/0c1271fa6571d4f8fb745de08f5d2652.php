<?php if (isset($component)) { $__componentOriginal333b9e857c198bd0078774586fa40930 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal333b9e857c198bd0078774586fa40930 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-dashboard-layout','data' => ['title' => 'Downloads','pretitle' => 'Download Center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('user-dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Downloads','pretitle' => 'Download Center']); ?>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Available Downloads</h2>
            </div>

            <div class="divide-y divide-slate-100">
                <?php $__empty_1 = true; $__currentLoopData = $downloads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="px-5 py-4">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Order #<?php echo e($order->id); ?></p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Delivered on <?php echo e($order->created_at?->format('Y-m-d H:i')); ?>

                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <?php if($order->delivery?->file_path): ?>
                                    <a href="<?php echo e(route('orders.delivery.download', $order)); ?>" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black transition">
                                        Download File
                                    </a>
                                <?php endif; ?>

                                <?php if($order->delivery?->delivery_link): ?>
                                    <a href="<?php echo e($order->delivery->delivery_link); ?>" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                                        Open Link
                                    </a>
                                <?php endif; ?>

                                <a href="<?php echo e(route('orders.show', $order)); ?>" class="inline-flex items-center rounded-lg border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 transition">
                                    Order Details
                                </a>
                            </div>
                        </div>

                        <?php if($order->delivery?->notes): ?>
                            <div class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                Note: <?php echo e($order->delivery->notes); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm font-semibold text-slate-700">No downloadable files yet.</p>
                        <p class="mt-1 text-xs text-slate-500">After your order is delivered, download button will appear here.</p>
                        <a href="<?php echo e(route('products.index')); ?>" class="mt-4 inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black transition">
                            Browse Products
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4">
            <?php echo e($downloads->links()); ?>

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
<?php /**PATH C:\xampp\htdocs\digify\resources\views/pages/downloads.blade.php ENDPATH**/ ?>