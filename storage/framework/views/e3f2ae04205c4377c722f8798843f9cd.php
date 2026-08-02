<?php if (isset($component)) { $__componentOriginal333b9e857c198bd0078774586fa40930 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal333b9e857c198bd0078774586fa40930 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-dashboard-layout','data' => ['title' => 'Subscriptions','pretitle' => 'Account']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('user-dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Subscriptions','pretitle' => 'Account']); ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Subscriptions</h3>
        </div>

        <div class="card-body">
            <div class="row row-cards">
                <?php $__empty_1 = true; $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $isActive = $item->subscription_expires_at && $item->subscription_expires_at->isFuture();
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold text-dark"><?php echo e($item->title); ?></div>
                                        <div class="text-secondary small">Order #<?php echo e($item->order_id); ?></div>
                                    </div>
                                    <span class="badge <?php echo e($isActive ? 'bg-success-lt' : 'bg-secondary-lt'); ?>">
                                        <?php echo e($isActive ? 'Active' : 'Expired'); ?>

                                    </span>
                                </div>

                                <div class="mt-4">
                                    <div class="d-flex justify-content-between border-bottom py-2">
                                        <span class="text-secondary">Starts</span>
                                        <span class="fw-medium"><?php echo e($item->subscription_starts_at?->format('M d, Y H:i') ?? 'Not set'); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between border-bottom py-2">
                                        <span class="text-secondary">Expires</span>
                                        <span class="fw-medium"><?php echo e($item->subscription_expires_at?->format('M d, Y H:i') ?? 'Not set'); ?></span>
                                    </div>
                                </div>

                                <?php if($item->entitlement_notes): ?>
                                    <div class="alert alert-info mt-3 mb-0"><?php echo e($item->entitlement_notes); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <a href="<?php echo e(route('orders.show', $item->order)); ?>" class="btn btn-outline-primary w-100">View Order</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12">
                        <div class="empty">
                            <p class="empty-title">No subscriptions yet</p>
                            <p class="empty-subtitle text-secondary">Delivered subscription products will appear here when admin adds the subscription dates.</p>
                            <div class="empty-action">
                                <a href="<?php echo e(route('products.index')); ?>" class="btn btn-primary">Browse Products</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if($subscriptions->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($subscriptions->links()); ?>

            </div>
        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\digify\resources\views/pages/account/subscriptions.blade.php ENDPATH**/ ?>