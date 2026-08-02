<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <?php
            $favicon = \App\Models\Setting::getValue('favicon', '');
            $seo = \App\Models\SeoSetting::query()->first();
            $routeProduct = request()->route('product');
            $isProductPage = $routeProduct instanceof \App\Models\Product;
            $appName = trim((string) config('app.name')) ?: 'OneDigify';
            $pageTitle = $isProductPage
                ? trim((string) $routeProduct->title).' | '.$appName
                : (trim((string) ($seo?->global_title ?? '')) ?: $appName);
            $pageDescription = $isProductPage
                ? \Illuminate\Support\Str::limit(trim(strip_tags((string) $routeProduct->description)), 155)
                : trim((string) ($seo?->meta_description ?? 'Premium digital products, subscriptions and services with reliable delivery.'));
            $pageDescription = $pageDescription !== '' ? $pageDescription : 'Premium digital products, subscriptions and services with reliable delivery.';
            $canonicalUrl = url()->current();
            $ogImagePath = $isProductPage ? ($routeProduct->image ?: $routeProduct->thumbnail_path) : ($seo?->og_image ?? '');
            $ogImageUrl = !empty($ogImagePath) ? asset(Storage::url($ogImagePath)) : null;
            $preloadImagePath = null;

            if ($isProductPage) {
                $preloadImagePath = $routeProduct->image ?: $routeProduct->thumbnail_path;
            } elseif (request()->routeIs('home')) {
                $preloadImagePath = isset($banners)
                    ? optional($banners->first())->image
                    : \App\Models\Banner::query()->where('is_active', true)->latest()->value('image');
            }
        ?>

        <title><?php echo e($pageTitle); ?></title>
        <meta name="description" content="<?php echo e($pageDescription); ?>">
        <?php if(!empty($seo?->meta_keywords) && !$isProductPage): ?>
            <meta name="keywords" content="<?php echo e($seo->meta_keywords); ?>">
        <?php endif; ?>
        <?php if(!empty($seo?->author_name)): ?>
            <meta name="author" content="<?php echo e($seo->author_name); ?>">
        <?php endif; ?>
        <link rel="canonical" href="<?php echo e($canonicalUrl); ?>">
        <meta property="og:type" content="<?php echo e($isProductPage ? 'product' : 'website'); ?>">
        <meta property="og:title" content="<?php echo e($pageTitle); ?>">
        <meta property="og:description" content="<?php echo e($pageDescription); ?>">
        <meta property="og:url" content="<?php echo e($canonicalUrl); ?>">
        <meta property="og:site_name" content="<?php echo e($appName); ?>">
        <meta name="twitter:card" content="<?php echo e($ogImageUrl ? 'summary_large_image' : 'summary'); ?>">
        <meta name="twitter:title" content="<?php echo e($pageTitle); ?>">
        <meta name="twitter:description" content="<?php echo e($pageDescription); ?>">

        <?php if(!empty($favicon)): ?>
            <link rel="icon" href="<?php echo e(Storage::url($favicon)); ?>">
        <?php endif; ?>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer">

        <?php if($ogImageUrl): ?>
            <meta property="og:image" content="<?php echo e($ogImageUrl); ?>">
            <meta name="twitter:image" content="<?php echo e($ogImageUrl); ?>">
        <?php endif; ?>

        <?php if(!empty($preloadImagePath)): ?>
            <link rel="preload" as="image" href="<?php echo e(Storage::url($preloadImagePath)); ?>" fetchpriority="high">
        <?php endif; ?>

        <?php echo $__env->yieldPushContent('head'); ?>

        <?php
            $manifestPath = public_path('build/manifest.json');
            $hasManifest = file_exists($manifestPath);
        ?>

        <?php if($hasManifest): ?>
            <?php
                $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
                $cssEntry = request()->routeIs('home') ? 'resources/css/home.css' : 'resources/css/site.css';
                $css = $manifest[$cssEntry]['file'] ?? null;
                $js = $manifest['resources/js/site.js']['file'] ?? null;
                $basePath = rtrim(request()->getBasePath(), '/');
            ?>

            <?php if($css): ?>
                <link rel="stylesheet" href="<?php echo e($basePath); ?>/build/<?php echo e($css); ?>">
            <?php endif; ?>

            <?php if($js): ?>
                <script type="module" src="<?php echo e($basePath); ?>/build/<?php echo e($js); ?>" defer></script>
            <?php endif; ?>
        <?php else: ?>
            <?php echo app('Illuminate\Foundation\Vite')(['resources/css/site.css', 'resources/js/site.js']); ?>
        <?php endif; ?>
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <?php if (isset($component)) { $__componentOriginal5b09c79149dfb771c232996af5f9dae4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b09c79149dfb771c232996af5f9dae4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash-messages','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('flash-messages'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5b09c79149dfb771c232996af5f9dae4)): ?>
<?php $attributes = $__attributesOriginal5b09c79149dfb771c232996af5f9dae4; ?>
<?php unset($__attributesOriginal5b09c79149dfb771c232996af5f9dae4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5b09c79149dfb771c232996af5f9dae4)): ?>
<?php $component = $__componentOriginal5b09c79149dfb771c232996af5f9dae4; ?>
<?php unset($__componentOriginal5b09c79149dfb771c232996af5f9dae4); ?>
<?php endif; ?>
        <?php echo $__env->make('layouts.partials.site-header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <main class="pt-14 pb-24 sm:pb-0">
            <?php echo e($slot); ?>

        </main>

        <?php echo $__env->make('layouts.partials.site-footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('layouts.partials.mobile-bottom-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('layouts.partials.whatsapp-widget', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\digify\resources\views/layouts/site.blade.php ENDPATH**/ ?>