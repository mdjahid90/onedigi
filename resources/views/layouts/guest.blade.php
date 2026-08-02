<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $favicon = \App\Models\Setting::getValue('favicon', '');
            $logoLight = \App\Models\Setting::getValue('logo_light', '');
        @endphp

        <title>{{ config('app.name', 'Laravel') }}</title>

        @if(!empty($favicon))
            <link rel="icon" href="{{ Storage::url($favicon) }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
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
                <link rel="stylesheet" href="{{ asset('build/' . $css) }}">
            @endif

            @if($js)
                <script src="{{ asset('build/' . $js) }}" defer></script>
            @endif
        @endunless
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <x-flash-messages />
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="{{ route('home') }}">
                    @if(!empty($logoLight))
                        <img src="{{ Storage::url($logoLight) }}" alt="Logo" class="h-20 w-auto" />
                    @else
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    @endif
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
