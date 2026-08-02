@props([
    'title' => 'Dashboard',
    'pretitle' => 'Account',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $favicon = \App\Models\Setting::getValue('favicon', '');
            $logoLight = \App\Models\Setting::getValue('logo_light', '');
            $seo = \App\Models\SeoSetting::query()->first();
            $basePath = rtrim(request()->getBasePath(), '/');
            $assetBase = ($basePath === '' ? '' : $basePath) . '/assets';
            $themeBase = ($basePath === '' ? '' : $basePath) . '/vendor/piprapay-theme';
            $manifestPath = public_path('build/manifest.json');
            $hasManifest = file_exists($manifestPath);
            $user = auth()->user();
            $userNotificationsReady = $user && \Illuminate\Support\Facades\Schema::hasTable('user_notifications');
            $userUnreadNotifications = $userNotificationsReady
                ? \App\Models\UserNotification::query()->where('user_id', $user->id)->whereNull('read_at')->count()
                : 0;
            $userRecentNotifications = $userNotificationsReady
                ? \App\Models\UserNotification::query()->where('user_id', $user->id)->latest()->take(20)->get()
                : collect();
        @endphp

        <title>{{ $title }} - {{ config('app.name', 'OneDigify') }}</title>

        @if(!empty($favicon))
            <link rel="icon" href="{{ Storage::url($favicon) }}">
        @endif

        @if(!empty($seo?->og_image))
            <meta property="og:image" content="{{ Storage::url($seo->og_image) }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

        @if($hasManifest)
            @php
                $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
                $css = $manifest['resources/css/app.css']['file'] ?? null;
                $js = $manifest['resources/js/app.js']['file'] ?? null;
            @endphp

            @if($css)
                <link rel="stylesheet" href="{{ $basePath }}/build/{{ $css }}">
            @endif

            @if($js)
                <script type="module" src="{{ $basePath }}/build/{{ $js }}" defer></script>
            @endif
        @else
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <link rel="stylesheet" href="{{ $assetBase }}/css/tabler.min.css">
        <link rel="stylesheet" href="{{ $assetBase }}/css/admin-tabler.css">
        <style>
            @import url('{{ $themeBase }}/css/inter.css');

            .user-dashboard-shell,
            .user-dashboard-shell .page,
            .user-dashboard-shell .page-wrapper,
            .user-dashboard-shell .page-header,
            .user-dashboard-shell .page-body,
            .user-dashboard-shell .user-page-body {
                background: #f5f7fb !important;
            }

            .user-dashboard-shell #sidebarMenu,
            .user-dashboard-shell #sidebarMenu .offcanvas-body,
            .user-dashboard-shell .admin-sidebar-nav {
                background: #fbfcfe !important;
            }

            .user-dashboard-shell .page-wrapper {
                margin-left: var(--digify-sidebar-width);
                min-height: calc(100vh - 64px);
            }

            .user-dashboard-shell .navbar-toggler {
                display: none;
            }

            .user-dashboard-shell .user-page-body {
                padding-top: 1rem;
                margin-top: 0 !important;
            }

            .user-dashboard-shell #sidebarMenu {
                z-index: 1050;
            }

            .user-dashboard-shell .offcanvas-backdrop {
                z-index: 1040;
            }

            .user-notification-menu {
                width: min(370px, calc(100vw - 1.5rem)) !important;
                max-width: calc(100vw - 1.5rem) !important;
                max-height: calc(100vh - 6rem) !important;
                overflow: hidden !important;
                padding-bottom: 0 !important;
            }

            .user-notification-list {
                max-height: min(430px, calc(100vh - 180px)) !important;
                overflow-x: hidden !important;
                overflow-y: auto !important;
                overscroll-behavior: contain;
                scrollbar-width: thin;
            }

            .user-notification-list::-webkit-scrollbar {
                width: 6px;
            }

            .user-notification-list::-webkit-scrollbar-track {
                background: transparent;
            }

            .user-notification-list::-webkit-scrollbar-thumb {
                background: rgba(103, 116, 138, 0.45);
                border-radius: 999px;
            }

            .user-notification-item {
                white-space: normal !important;
            }

            .user-notification-item .text-truncate {
                min-width: 0;
            }

            @media (max-width: 767.98px) {
                .user-dashboard-shell .page-wrapper {
                    margin-left: 0;
                }

                .user-dashboard-shell .navbar-toggler {
                    display: inline-flex;
                }

                .user-notification-menu {
                    position: fixed !important;
                    inset: 4.75rem 0.75rem auto 0.75rem !important;
                    width: auto !important;
                    max-width: none !important;
                    transform: none !important;
                }

                .user-notification-list {
                    max-height: calc(100vh - 11rem) !important;
                }
            }
        </style>

        @stack('styles')
    </head>
    <body class="layout-fluid admin-theme user-dashboard-shell antialiased">
        <x-flash-messages />

        <div class="page">
            <header class="navbar navbar-expand-md sticky-top d-print-none py-2 admin-navbar-shell">
                <div class="container-xl">
                    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-label="Open account menu">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                        <a href="{{ route('dashboard') }}" aria-label="{{ config('app.name') }}">
                            @if(!empty($logoLight))
                                <img src="{{ Storage::url($logoLight) }}" alt="Logo" style="height: 32px;">
                            @else
                                <img src="{{ $themeBase }}/images/logo-light.png" alt="Logo" style="height: 32px;">
                            @endif
                        </a>
                    </div>

                    <div class="navbar-nav flex-row order-md-last">
                        <div class="nav-item me-2">
                            <a href="{{ route('home') }}" class="nav-link px-2" aria-label="Storefront">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 12l9 -9l9 9"/>
                                    <path d="M5 10v10h14v-10"/>
                                    <path d="M9 20v-6h6v6"/>
                                </svg>
                            </a>
                        </div>
                        <div class="nav-item me-2">
                            <a href="{{ route('cart') }}" class="nav-link px-2" aria-label="Cart">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                    <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                    <path d="M17 17h-11v-14h-2"/>
                                    <path d="M6 5l14 1l-1 7h-13"/>
                                </svg>
                            </a>
                        </div>
                        @auth
                            <div class="nav-item dropdown me-2">
                                <a href="#" class="nav-link px-2 position-relative" data-bs-toggle="dropdown" aria-label="Open notifications" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/>
                                        <path d="M9 17v1a3 3 0 0 0 6 0v-1"/>
                                    </svg>
                                    @if($userUnreadNotifications > 0)
                                        <span class="badge bg-danger admin-notification-badge">{{ $userUnreadNotifications > 99 ? '99+' : $userUnreadNotifications }}</span>
                                    @endif
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow user-notification-menu">
                                    <div class="dropdown-header d-flex align-items-center justify-content-between">
                                        <span>Notifications</span>
                                        <span class="badge bg-primary-lt">{{ $userUnreadNotifications }} unread</span>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <div class="user-notification-list">
                                        @forelse($userRecentNotifications as $notification)
                                            @php
                                                $notificationUnread = $notification->read_at === null;
                                                $notificationSeverity = $notification->severity === 'danger' ? 'danger' : ($notification->severity === 'warning' ? 'warning' : ($notification->severity === 'success' ? 'success' : 'primary'));
                                            @endphp
                                            <a href="{{ route('notifications.open', $notification) }}" class="dropdown-item user-notification-item {{ $notificationUnread ? '' : 'text-secondary' }}">
                                                <div class="d-flex">
                                                    <span class="status-dot me-2 {{ $notificationUnread ? 'status-dot-animated bg-' . $notificationSeverity : 'bg-secondary' }}"></span>
                                                    <div class="text-truncate">
                                                        <div class="fw-semibold text-truncate">{{ $notification->title }}</div>
                                                        @if($notification->body)
                                                            <div class="small text-secondary text-truncate">{{ $notification->body }}</div>
                                                        @endif
                                                        <div class="small text-secondary">{{ $notification->created_at?->diffForHumans() }}</div>
                                                    </div>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="dropdown-item text-secondary">No notifications yet.</div>
                                        @endforelse
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a href="{{ route('notifications.index') }}" class="dropdown-item justify-content-center text-primary fw-semibold">
                                        View all notifications
                                    </a>
                                </div>
                            </div>
                        @endauth
                        <div class="nav-item dropdown">
                            @auth
                                <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Open user menu" aria-expanded="false">
                                    <span class="avatar avatar-sm admin-avatar-chip">
                                        {{ strtoupper(substr((string) $user->name, 0, 2)) }}
                                    </span>
                                    <div class="d-none d-xl-block ps-2">
                                        <div class="admin-user-name">{{ $user->name }}</div>
                                        <div class="mt-1 small text-secondary">Customer</div>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <a href="{{ route('dashboard') }}" class="dropdown-item">Dashboard</a>
                                    <a href="{{ route('orders') }}" class="dropdown-item">My Orders</a>
                                    <a href="{{ route('downloads.index') }}" class="dropdown-item">Downloads</a>
                                    <a href="{{ route('subscriptions.index') }}" class="dropdown-item">Subscriptions</a>
                                    <a href="{{ route('licenses.index') }}" class="dropdown-item">Licenses</a>
                                    <a href="{{ route('profile.edit') }}" class="dropdown-item">Profile</a>
                                    <a href="{{ route('refunds.index') }}" class="dropdown-item">Refunds</a>
                                    @if($user->is_admin)
                                        <div class="dropdown-divider"></div>
                                        <a href="{{ route('admin.dashboard') }}" class="dropdown-item">Admin Panel</a>
                                    @endif
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </div>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Login</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </header>

            <div class="offcanvas-md offcanvas-start sidebar" tabindex="-1" id="sidebarMenu">
                <div class="offcanvas-header d-md-none">
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body p-0">
                    <x-user-dashboard-sidebar />
                </div>
            </div>

            <div class="page-wrapper">
                <div class="page-header d-print-none">
                    <div class="container-xl">
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <div class="page-pretitle">{{ $pretitle }}</div>
                                <h2 class="page-title">{{ $title }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <main class="page-body user-page-body">
                    <div class="container-xl">
                        <div class="admin-page-stack">
                            {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <script src="{{ $assetBase }}/js/tabler.min.js" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const sidebar = document.getElementById('sidebarMenu');
                const toggleButton = document.querySelector('[data-bs-target="#sidebarMenu"]');
                const closeButton = sidebar?.querySelector('[data-bs-dismiss="offcanvas"]');
                let backdrop = null;

                if (!sidebar || !toggleButton) {
                    return;
                }

                const isMobile = () => window.matchMedia('(max-width: 767.98px)').matches;

                const closeSidebar = () => {
                    sidebar.classList.remove('show');
                    sidebar.style.visibility = '';
                    sidebar.removeAttribute('aria-modal');
                    sidebar.removeAttribute('role');
                    document.body.classList.remove('overflow-hidden');
                    backdrop?.remove();
                    backdrop = null;
                };

                const openSidebar = (event) => {
                    if (!isMobile()) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    sidebar.classList.add('show');
                    sidebar.style.visibility = 'visible';
                    sidebar.setAttribute('aria-modal', 'true');
                    sidebar.setAttribute('role', 'dialog');
                    document.body.classList.add('overflow-hidden');

                    if (!backdrop) {
                        backdrop = document.createElement('div');
                        backdrop.className = 'offcanvas-backdrop fade show';
                        backdrop.addEventListener('click', closeSidebar);
                        document.body.appendChild(backdrop);
                    }
                };

                toggleButton.addEventListener('click', openSidebar, true);
                closeButton?.addEventListener('click', function (event) {
                    event.preventDefault();
                    closeSidebar();
                });

                sidebar.querySelectorAll('.nav-link').forEach((link) => {
                    link.addEventListener('click', closeSidebar);
                });

                window.addEventListener('resize', function () {
                    if (!isMobile()) {
                        closeSidebar();
                    }
                });
            });
        </script>
        @stack('scripts')
    </body>
</html>
