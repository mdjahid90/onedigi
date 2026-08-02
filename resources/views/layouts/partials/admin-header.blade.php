@php
    $logoLight = \App\Models\Setting::getValue('logo_light', '');
    $basePath = rtrim(request()->getBasePath(), '/');
    $adminThemeBase = ($basePath === '' ? '' : $basePath) . '/vendor/piprapay-theme';
    $adminNotificationsReady = \Illuminate\Support\Facades\Schema::hasTable('admin_notifications');
    $adminUnreadNotifications = $adminNotificationsReady
        ? \App\Models\AdminNotification::query()->whereNull('read_at')->count()
        : 0;
    $adminRecentNotifications = $adminNotificationsReady
        ? \App\Models\AdminNotification::query()->latest()->take(20)->get()
        : collect();
@endphp

@once
    <style>
        .admin-notification-menu {
            width: min(370px, calc(100vw - 1.5rem)) !important;
            max-width: calc(100vw - 1.5rem) !important;
            max-height: calc(100vh - 6rem) !important;
            overflow: hidden !important;
            padding-bottom: 0 !important;
        }

        .admin-notification-list {
            max-height: min(430px, calc(100vh - 180px)) !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            overscroll-behavior: contain;
            scrollbar-width: thin;
        }

        .admin-notification-list::-webkit-scrollbar {
            width: 6px;
        }

        .admin-notification-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .admin-notification-list::-webkit-scrollbar-thumb {
            background: rgba(103, 116, 138, 0.45);
            border-radius: 999px;
        }

        .admin-notification-item {
            white-space: normal !important;
        }

        .admin-notification-item .text-truncate {
            min-width: 0;
        }

        @media (max-width: 575.98px) {
            .admin-notification-menu {
                position: fixed !important;
                inset: 4.75rem 0.75rem auto 0.75rem !important;
                width: auto !important;
                max-width: none !important;
                transform: none !important;
            }

            .admin-notification-list {
                max-height: calc(100vh - 11rem) !important;
            }
        }
    </style>
@endonce

<header class="navbar navbar-expand-md sticky-top d-print-none py-2 admin-navbar-shell">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <a href="{{ route('admin.dashboard') }}" aria-label="Admin">
                @if(!empty($logoLight))
                    <img src="{{ Storage::url($logoLight) }}" alt="Logo" style="height: 32px;">
                @else
                    <img src="{{ $adminThemeBase }}/images/logo-light.png" alt="Logo" style="height: 32px;">
                @endif
            </a>
        </div>

        <div class="navbar-nav flex-row order-md-last">
            <div class="nav-item dropdown me-2">
                <a href="#" class="nav-link px-2 position-relative" data-bs-toggle="dropdown" aria-label="Open notifications" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/>
                        <path d="M9 17v1a3 3 0 0 0 6 0v-1"/>
                    </svg>
                    @if($adminUnreadNotifications > 0)
                        <span class="badge bg-danger admin-notification-badge">{{ $adminUnreadNotifications > 99 ? '99+' : $adminUnreadNotifications }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow admin-notification-menu">
                    <div class="dropdown-header d-flex align-items-center justify-content-between">
                        <span>Notifications</span>
                        <span class="badge bg-primary-lt">{{ $adminUnreadNotifications }} unread</span>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="admin-notification-list">
                        @forelse($adminRecentNotifications as $notification)
                            @php
                                $notificationUnread = $notification->read_at === null;
                                $notificationSeverity = $notification->severity === 'danger' ? 'danger' : ($notification->severity === 'warning' ? 'warning' : ($notification->severity === 'success' ? 'success' : 'primary'));
                            @endphp
                            <a href="{{ route('admin.notifications.open', $notification) }}" class="dropdown-item admin-notification-item {{ $notificationUnread ? '' : 'text-secondary' }}">
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
                    <a href="{{ route('admin.notifications.index') }}" class="dropdown-item justify-content-center text-primary fw-semibold">
                        View all notifications
                    </a>
                </div>
            </div>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Open user menu" aria-expanded="false">
                    <span class="avatar avatar-sm admin-avatar-chip">
                        {{ strtoupper(substr((string) Auth::user()->name, 0, 2)) }}
                    </span>
                    <div class="d-none d-xl-block ps-2">
                        <div class="admin-user-name">{{ Auth::user()->name }}</div>
                        <div class="mt-1 small text-secondary">{{ Auth::user()->is_admin ? 'Admin' : 'Staff' }}</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('dashboard') }}" class="dropdown-item">Dashboard</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
