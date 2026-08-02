<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
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
        @endphp

        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $pageDescription }}">
        @if(!empty($seo?->meta_keywords) && !$isProductPage)
            <meta name="keywords" content="{{ $seo->meta_keywords }}">
        @endif
        @if(!empty($seo?->author_name))
            <meta name="author" content="{{ $seo->author_name }}">
        @endif
        <link rel="canonical" href="{{ $canonicalUrl }}">
        <meta property="og:type" content="{{ $isProductPage ? 'product' : 'website' }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:site_name" content="{{ $appName }}">
        <meta name="twitter:card" content="{{ $ogImageUrl ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $pageTitle }}">
        <meta name="twitter:description" content="{{ $pageDescription }}">

        @if(!empty($favicon))
            <link rel="icon" href="{{ Storage::url($favicon) }}">
        @endif

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer">

        @if($ogImageUrl)
            <meta property="og:image" content="{{ $ogImageUrl }}">
            <meta name="twitter:image" content="{{ $ogImageUrl }}">
        @endif

        @if(!empty($preloadImagePath))
            <link rel="preload" as="image" href="{{ Storage::url($preloadImagePath) }}" fetchpriority="high">
        @endif

        @stack('head')

        @php
            $manifestPath = public_path('build/manifest.json');
            $hasManifest = file_exists($manifestPath);
        @endphp

        @if($hasManifest)
            @php
                $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
                $cssEntry = request()->routeIs('home') ? 'resources/css/home.css' : 'resources/css/site.css';
                $css = $manifest[$cssEntry]['file'] ?? null;
                $js = $manifest['resources/js/site.js']['file'] ?? null;
                $basePath = rtrim(request()->getBasePath(), '/');
            @endphp

            @if($css)
                <link rel="stylesheet" href="{{ $basePath }}/build/{{ $css }}">
            @endif

            @if($js)
                <script type="module" src="{{ $basePath }}/build/{{ $js }}" defer></script>
            @endif
        @else
            @vite(['resources/css/site.css', 'resources/js/site.js'])
        @endif
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <x-flash-messages />
        @include('layouts.partials.site-header')

        <main class="pt-14 pb-24 sm:pb-0">
            {{ $slot }}
        </main>

        @include('layouts.partials.site-footer')
        @include('layouts.partials.mobile-bottom-nav')
        @include('layouts.partials.whatsapp-widget')
    </body>
</html>
