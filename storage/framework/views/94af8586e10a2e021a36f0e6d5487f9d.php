<?php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();
    $menuGroups = $user ? [
        'Overview' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'dashboard'],
            ['label' => 'Notifications', 'route' => 'notifications.index', 'active' => 'notifications.*', 'icon' => 'notifications'],
            ['label' => 'My Orders', 'route' => 'orders', 'active' => 'orders*', 'icon' => 'orders'],
            ['label' => 'Downloads', 'route' => 'downloads.index', 'active' => 'downloads.*', 'icon' => 'download'],
            ['label' => 'Subscriptions', 'route' => 'subscriptions.index', 'active' => 'subscriptions.*', 'icon' => 'subscriptions'],
            ['label' => 'Licenses', 'route' => 'licenses.index', 'active' => 'licenses.*', 'icon' => 'licenses'],
            ['label' => 'Tickets', 'route' => 'tickets.index', 'active' => 'tickets.*', 'icon' => 'tickets'],
            ['label' => 'Profile', 'route' => 'profile.edit', 'active' => 'profile.*', 'icon' => 'profile'],
            ['label' => 'Refunds', 'route' => 'refunds.index', 'active' => 'refunds.*', 'icon' => 'refunds'],
        ],
        'Store' => [
            ['label' => 'Products', 'route' => 'products.index', 'active' => 'products.*', 'icon' => 'products'],
            ['label' => 'Categories', 'route' => 'categories', 'active' => 'categories', 'icon' => 'categories'],
            ['label' => 'Cart', 'route' => 'cart', 'active' => 'cart', 'icon' => 'cart'],
        ],
    ] : [
        'Store' => [
            ['label' => 'Home', 'route' => 'home', 'active' => 'home', 'icon' => 'dashboard'],
            ['label' => 'Products', 'route' => 'products.index', 'active' => 'products.*', 'icon' => 'products'],
            ['label' => 'Cart', 'route' => 'cart', 'active' => 'cart', 'icon' => 'cart'],
        ],
    ];

    if ($user?->is_admin) {
        $menuGroups['Admin'] = [
            ['label' => 'Admin Panel', 'route' => 'admin.dashboard', 'active' => 'admin.*', 'icon' => 'shield'],
        ];
    }

    $renderIcon = static function (string $icon): string {
        $attrs = 'xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler"';

        return match ($icon) {
            'dashboard' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M4 4h6v8h-6z\"/><path d=\"M14 4h6v4h-6z\"/><path d=\"M14 12h6v8h-6z\"/><path d=\"M4 16h6v4h-6z\"/></svg>",
            'notifications' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6\"/><path d=\"M9 17v1a3 3 0 0 0 6 0v-1\"/></svg>",
            'orders' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2\"/><path d=\"M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z\"/><path d=\"M9 12h6\"/><path d=\"M9 16h6\"/></svg>",
            'download' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2\"/><path d=\"M7 11l5 5l5 -5\"/><path d=\"M12 4l0 12\"/></svg>",
            'subscriptions' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M7 4h10a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2z\"/><path d=\"M9 8h6\"/><path d=\"M9 12h6\"/><path d=\"M9 16h4\"/></svg>",
            'licenses' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M15 7a2 2 0 1 0 -4 0a2 2 0 0 0 4 0z\"/><path d=\"M14 9l6 6l-3 3l-6 -6\"/><path d=\"M10 12l-4 4l-2 -2l4 -4\"/><path d=\"M7 17l2 2\"/></svg>",
            'tickets' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M15 5v2\"/><path d=\"M15 11v2\"/><path d=\"M15 17v2\"/><path d=\"M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-3a2 2 0 0 0 0 -4v-3a2 2 0 0 1 2 -2\"/></svg>",
            'profile' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0\"/><path d=\"M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2\"/></svg>",
            'refunds' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M9 14l-4 -4l4 -4\"/><path d=\"M5 10h11a4 4 0 1 1 0 8h-1\"/><path d=\"M12 4v2\"/><path d=\"M12 18v2\"/></svg>",
            'products' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M7 10l5 -6l5 6\"/><path d=\"M21 10l-2 9a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2l-2 -9z\"/><path d=\"M9 15h6\"/></svg>",
            'categories' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M14 4h6v6h-6z\"/><path d=\"M4 14h6v6h-6z\"/><path d=\"M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0\"/><path d=\"M7 7m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0\"/></svg>",
            'cart' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0\"/><path d=\"M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0\"/><path d=\"M17 17h-11v-14h-2\"/><path d=\"M6 5l14 1l-1 7h-13\"/></svg>",
            'shield' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M12 3l7 4v5c0 5 -3 8 -7 9c-4 -1 -7 -4 -7 -9v-5z\"/><path d=\"M9 12l2 2l4 -4\"/></svg>",
            default => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><circle cx=\"12\" cy=\"12\" r=\"9\"/></svg>",
        };
    };
?>

<nav class="admin-sidebar-nav" aria-label="Account navigation">
    <ul class="nav flex-column admin-sidebar-list">
        <?php $__currentLoopData = $menuGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupTitle => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="admin-nav-section-title"><?php echo e($groupTitle); ?></li>
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(! Route::has($item['route'])) continue; ?>
                <li class="nav-item">
                    <a href="<?php echo e(route($item['route'])); ?>" class="nav-link <?php echo e(request()->routeIs($item['active']) ? 'active' : ''); ?>">
                        <span class="nav-link-icon"><?php echo $renderIcon($item['icon']); ?></span>
                        <span class="nav-link-title"><?php echo e($item['label']); ?></span>
                    </a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</nav>
<?php /**PATH C:\xampp\htdocs\digify\resources\views/components/user-dashboard-sidebar.blade.php ENDPATH**/ ?>