<?php if (isset($component)) { $__componentOriginal333b9e857c198bd0078774586fa40930 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal333b9e857c198bd0078774586fa40930 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-dashboard-layout','data' => ['title' => 'Notifications','pretitle' => 'Account']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('user-dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Notifications','pretitle' => 'Account']); ?>
    <div class="page-body">
        <div class="row row-cards mb-3">
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase fw-semibold">Total</div>
                        <div class="h2 mb-0"><?php echo e(number_format((int) $totalNotifications)); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase fw-semibold">Unread</div>
                        <div class="h2 mb-0 text-primary"><?php echo e(number_format((int) $unreadNotifications)); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase fw-semibold">Read</div>
                        <div class="h2 mb-0"><?php echo e(number_format((int) $readNotifications)); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body border-bottom py-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-auto">
                        <div class="btn-list">
                            <?php $__currentLoopData = ['all' => 'All', 'unread' => 'Unread', 'read' => 'Read']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('notifications.index', array_filter(['status' => $key, 'search' => $search]))); ?>" class="btn btn-sm <?php echo e($status === $key ? 'btn-primary' : 'btn-outline-secondary'); ?>">
                                    <?php echo e($label); ?>

                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <div class="col-md ms-md-auto">
                        <form method="GET" action="<?php echo e(route('notifications.index')); ?>" class="input-group">
                            <input type="hidden" name="status" value="<?php echo e($status); ?>">
                            <input type="text" name="search" value="<?php echo e($search); ?>" class="form-control" placeholder="Search notifications">
                            <button class="btn btn-primary" type="submit">Search</button>
                            <?php if($search !== ''): ?>
                                <a href="<?php echo e(route('notifications.index', ['status' => $status])); ?>" class="btn btn-outline-secondary">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-auto">
                        <form method="POST" action="<?php echo e(route('notifications.read_all')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-primary" <?php if($unreadNotifications < 1): echo 'disabled'; endif; ?>>
                                Mark all as read
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $notificationUnread = $notification->read_at === null;
                        $notificationSeverity = match ($notification->severity) {
                            'danger' => 'danger',
                            'warning' => 'warning',
                            'success' => 'success',
                            default => 'primary',
                        };
                    ?>
                    <div class="list-group-item <?php echo e($notificationUnread ? 'bg-primary-lt' : ''); ?>">
                        <div class="row align-items-center g-3">
                            <div class="col-auto">
                                <span class="status-dot <?php echo e($notificationUnread ? 'status-dot-animated bg-' . $notificationSeverity : 'bg-secondary'); ?>"></span>
                            </div>
                            <div class="col">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="fw-semibold"><?php echo e($notification->title); ?></div>
                                    <?php if($notificationUnread): ?>
                                        <span class="badge bg-primary-lt">Unread</span>
                                    <?php endif; ?>
                                    <span class="text-secondary small"><?php echo e($notification->created_at?->diffForHumans()); ?></span>
                                </div>
                                <?php if($notification->body): ?>
                                    <div class="text-secondary mt-1"><?php echo e($notification->body); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <a href="<?php echo e(route('notifications.open', $notification)); ?>" class="btn btn-sm btn-outline-secondary">
                                    Open
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="empty">
                        <p class="empty-title">No notifications found</p>
                        <p class="empty-subtitle text-secondary">Order updates, support replies, deliveries, and refund updates will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($notifications->hasPages()): ?>
                <div class="card-footer d-flex justify-content-center justify-content-sm-end">
                    <?php echo e($notifications->onEachSide(1)->links()); ?>

                </div>
            <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\digify\resources\views/pages/notifications/index.blade.php ENDPATH**/ ?>