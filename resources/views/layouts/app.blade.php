<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $favicon = \App\Models\Setting::getValue('favicon', '');
            $seo = \App\Models\SeoSetting::query()->first();
            $basePath = rtrim(request()->getBasePath(), '/');
            $adminAssetBase = ($basePath === '' ? '' : $basePath) . '/assets';
            $adminThemeBase = ($basePath === '' ? '' : $basePath) . '/vendor/piprapay-theme';
            $isAdminRoute = request()->routeIs('admin.*');
        @endphp

        <title>{{ config('app.name', 'OneDigify') }}</title>

        @if(!empty($favicon))
            <link rel="icon" href="{{ Storage::url($favicon) }}">
        @endif

        @if(!empty($seo?->og_image))
            <meta property="og:image" content="{{ Storage::url($seo->og_image) }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @env('local')
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endenv

        @unless(app()->environment('local'))
            @php
                $manifestPath = public_path('build/manifest.json');
                $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
                $css = $manifest['resources/css/app.css']['file'] ?? null;
                $js = $manifest['resources/js/app.js']['file'] ?? null;
            @endphp

            @if($css)
                <link rel="stylesheet" href="{{ $basePath }}/build/{{ $css }}">
            @endif

            @if($js)
                <script type="module" src="{{ $basePath }}/build/{{ $js }}" defer></script>
            @endif
        @endunless

        @if($isAdminRoute)
            <link rel="stylesheet" href="{{ $adminAssetBase }}/css/tabler.min.css">
            <link rel="stylesheet" href="{{ $adminAssetBase }}/css/admin-tabler.css">
            <style>
                @import url('{{ $adminThemeBase }}/css/inter.css');
            </style>
        @endif

        @stack('styles')
    </head>
    <body class="{{ $isAdminRoute ? 'layout-fluid admin-theme antialiased' : 'font-sans antialiased' }}">
        <x-flash-messages />

        @if ($isAdminRoute)
            <div class="page">
                @include('layouts.partials.admin-header')

                <div class="offcanvas-md offcanvas-start sidebar" tabindex="-1" id="sidebarMenu">
                    <div class="offcanvas-header d-md-none">
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body p-0">
                        @include('layouts.partials.admin-sidebar')
                    </div>
                </div>

                <div class="page-wrapper">
                    <main>
                        {{ $slot }}
                    </main>

                    @include('layouts.partials.admin-footer')
                </div>
            </div>
        @else
            @include('layouts.partials.site-header')

            <div class="min-h-screen bg-gray-100 pt-14 pb-24 sm:pb-0">
                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>

            @include('layouts.partials.site-footer')
            @include('layouts.partials.mobile-bottom-nav')
        @endif

        @if($isAdminRoute)
            <script src="{{ $adminAssetBase }}/js/tabler.min.js" defer></script>
        @endif

        @stack('scripts')
    </body>
</html>
