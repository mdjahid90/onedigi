<?php if (isset($component)) { $__componentOriginal333b9e857c198bd0078774586fa40930 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal333b9e857c198bd0078774586fa40930 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-dashboard-layout','data' => ['title' => 'My Orders','pretitle' => 'Orders']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('user-dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My Orders','pretitle' => 'Orders']); ?>
    <div class="orders-page-stack">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
            <div class="fw-semibold text-dark"><?php echo e(__('ui.my_orders')); ?></div>
            <div class="btn-list">
                <a href="<?php echo e(route('downloads.index')); ?>" class="btn btn-outline-secondary btn-sm">Download Center</a>
                <a href="<?php echo e(route('products.index')); ?>" class="btn btn-primary btn-sm"><?php echo e(__('ui.browse_products')); ?></a>
            </div>
        </div>

        <div class="card mt-3 d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-vcenter card-table text-nowrap">
                    <thead>
                        <tr>
                            <th><?php echo e(__('ui.order')); ?></th>
                            <th><?php echo e(__('ui.status')); ?></th>
                            <th><?php echo e(__('ui.amount')); ?></th>
                            <th><?php echo e(__('ui.date')); ?></th>
                            <th class="text-end"><?php echo e(__('ui.action')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="fw-semibold text-dark">#<?php echo e($order->id); ?></td>
                                <td>
                                    <span class="badge <?php echo e($order->status === 'DELIVERED' ? 'bg-success-lt' : ($order->status === 'CANCELLED' ? 'bg-danger-lt' : 'bg-warning-lt')); ?>">
                                        <?php echo e($order->status); ?>

                                    </span>
                                </td>
                                <td><?php if (isset($component)) { $__componentOriginal3c51c5b308d311657bfae6be692a1470 = $component; } ?>
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
                                <td class="text-secondary"><?php echo e($order->created_at?->format('Y-m-d H:i')); ?></td>
                                <td class="text-end">
                                    <a href="<?php echo e(route('orders.show', $order)); ?>" class="btn btn-primary btn-sm"><?php echo e(__('ui.view')); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td class="text-secondary text-center py-5" colspan="5"><?php echo e(__('ui.no_orders_yet')); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-md-none mt-3">
            <div class="vstack gap-3">
                <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="text-secondary small text-uppercase fw-semibold"><?php echo e(__('ui.order')); ?></div>
                                    <div class="h3 m-0">#<?php echo e($order->id); ?></div>
                                </div>
                                <span class="badge <?php echo e($order->status === 'DELIVERED' ? 'bg-success-lt' : ($order->status === 'CANCELLED' ? 'bg-danger-lt' : 'bg-warning-lt')); ?>">
                                    <?php echo e($order->status); ?>

                                </span>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-6">
                                    <div class="text-secondary small text-uppercase fw-semibold"><?php echo e(__('ui.amount')); ?></div>
                                    <div class="fw-semibold text-dark"><?php if (isset($component)) { $__componentOriginal3c51c5b308d311657bfae6be692a1470 = $component; } ?>
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
<?php endif; ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-secondary small text-uppercase fw-semibold"><?php echo e(__('ui.date')); ?></div>
                                    <div class="fw-semibold text-dark"><?php echo e($order->created_at?->format('M d, Y')); ?></div>
                                    <div class="small text-secondary"><?php echo e($order->created_at?->format('H:i')); ?></div>
                                </div>
                            </div>

                            <a href="<?php echo e(route('orders.show', $order)); ?>" class="btn btn-primary w-100 mt-3"><?php echo e(__('ui.view')); ?></a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="card">
                        <div class="card-body text-center text-secondary py-5"><?php echo e(__('ui.no_orders_yet')); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4">
            <?php echo e($orders->links()); ?>

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
<?php /**PATH C:\xampp\htdocs\digify\resources\views/pages/orders.blade.php ENDPATH**/ ?>