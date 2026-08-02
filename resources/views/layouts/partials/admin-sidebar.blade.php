@php
    use Illuminate\Support\Facades\Route;

    $menuGroups = [
        'Overview' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'dashboard'],
            ['label' => 'Analytics', 'route' => 'admin.analytics.index', 'active' => 'admin.analytics.*', 'icon' => 'analytics'],
            ['label' => 'Notifications', 'route' => 'admin.notifications.index', 'active' => 'admin.notifications.*', 'icon' => 'notifications'],
            ['label' => 'Messages', 'route' => 'admin.messages.index', 'active' => 'admin.messages.*', 'icon' => 'messages'],
            ['label' => 'Tickets', 'route' => 'admin.tickets.index', 'active' => 'admin.tickets.*', 'icon' => 'tickets'],
        ],
        'Commerce' => [
            ['label' => 'Orders', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*', 'icon' => 'orders'],
            ['label' => 'Transactions', 'route' => 'admin.transactions.index', 'active' => 'admin.transactions.*', 'icon' => 'transactions'],
            ['label' => 'Refund Requests', 'route' => 'admin.refund_requests.index', 'active' => 'admin.refund_requests.*', 'icon' => 'refunds'],
            ['label' => 'Customers', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'users'],
            ['label' => 'Gateways', 'route' => 'admin.gateways.index', 'active' => 'admin.gateways.*', 'icon' => 'gateways'],
        ],
        'Catalog' => [
            ['label' => 'Products', 'route' => 'admin.products.index', 'active' => 'admin.products.*', 'icon' => 'products'],
            ['label' => 'Categories', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*', 'icon' => 'categories'],
            ['label' => 'Reviews', 'route' => 'admin.reviews.index', 'active' => 'admin.reviews.*', 'icon' => 'reviews'],
        ],
        'Content' => [
            ['label' => 'Pages', 'route' => 'admin.pages.index', 'active' => 'admin.pages.*', 'icon' => 'pages'],
            ['label' => 'Email Templates', 'route' => 'admin.email_templates.index', 'active' => 'admin.email_templates.*', 'icon' => 'email'],
            ['label' => 'Footer', 'route' => 'admin.footer.edit', 'active' => 'admin.footer.*', 'icon' => 'footer'],
        ],
        'Marketing' => [
            ['label' => 'Banners', 'route' => 'admin.marketing.banners.index', 'active' => 'admin.marketing.banners.*', 'icon' => 'banners'],
            ['label' => 'SEO', 'route' => 'admin.marketing.seo', 'active' => 'admin.marketing.seo*', 'icon' => 'seo'],
            ['label' => 'Sitemap', 'route' => 'admin.marketing.sitemap', 'active' => 'admin.marketing.sitemap*', 'icon' => 'sitemap'],
        ],
        'System' => [
            ['label' => 'Settings', 'route' => 'admin.settings', 'active' => 'admin.settings*', 'icon' => 'settings'],
            ['label' => 'Branding', 'route' => 'admin.branding.index', 'active' => 'admin.branding.*', 'icon' => 'branding'],
            ['label' => 'Currency', 'route' => 'admin.currency.edit', 'active' => 'admin.currency.*', 'icon' => 'currency'],
            ['label' => 'WhatsApp Widget', 'route' => 'admin.whatsapp-widget.edit', 'active' => 'admin.whatsapp-widget.*', 'icon' => 'whatsapp'],
        ],
    ];

    $renderIcon = static function (string $icon): string {
        $attrs = 'xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler"';

        return match ($icon) {
            'dashboard' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M4 4h6v8h-6z\"/><path d=\"M14 4h6v4h-6z\"/><path d=\"M14 12h6v8h-6z\"/><path d=\"M4 16h6v4h-6z\"/></svg>",
            'analytics' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M3 3v18h18\"/><path d=\"M7 16l4 -4l4 3l5 -7\"/></svg>",
            'notifications' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6\"/><path d=\"M9 17v1a3 3 0 0 0 6 0v-1\"/></svg>",
            'messages' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M8 9h8\"/><path d=\"M8 13h6\"/><path d=\"M9 18h-4a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h14a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-4l-3 3z\"/></svg>",
            'tickets' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M15 5v2\"/><path d=\"M15 11v2\"/><path d=\"M15 17v2\"/><path d=\"M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-3a2 2 0 0 0 0 -4v-3a2 2 0 0 1 2 -2\"/></svg>",
            'orders' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2\"/><path d=\"M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z\"/><path d=\"M9 12h6\"/><path d=\"M9 16h6\"/></svg>",
            'transactions' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M7 7h10v10h-10z\"/><path d=\"M3 11h4\"/><path d=\"M17 11h4\"/><path d=\"M3 15h4\"/><path d=\"M17 15h4\"/><path d=\"M11 3v4\"/><path d=\"M15 3v4\"/><path d=\"M11 17v4\"/><path d=\"M15 17v4\"/></svg>",
            'refunds' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M9 14l-4 -4l4 -4\"/><path d=\"M5 10h11a4 4 0 1 1 0 8h-1\"/><path d=\"M12 4v2\"/><path d=\"M12 18v2\"/></svg>",
            'users' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M9 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0\"/><path d=\"M3 21v-2a4 4 0 0 1 4 -4h4\"/><path d=\"M16 19h6\"/><path d=\"M19 16v6\"/></svg>",
            'gateways' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M3 5m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z\"/><path d=\"M3 10h18\"/><path d=\"M7 15h.01\"/><path d=\"M11 15h2\"/></svg>",
            'products' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M7 10l5 -6l5 6\"/><path d=\"M21 10l-2 9a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2l-2 -9z\"/><path d=\"M9 15h6\"/></svg>",
            'categories' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M14 4h6v6h-6z\"/><path d=\"M4 14h6v6h-6z\"/><path d=\"M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0\"/><path d=\"M7 7m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0\"/></svg>",
            'reviews' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M12 17.75l-6.172 3.245l1.179 -6.873l-4.993 -4.867l6.9 -1.002l3.086 -6.253l3.086 6.253l6.9 1.002l-4.993 4.867l1.179 6.873z\"/></svg>",
            'pages' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M14 3v4a1 1 0 0 0 1 1h4\"/><path d=\"M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z\"/><path d=\"M9 13h6\"/><path d=\"M9 17h3\"/></svg>",
            'email' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z\"/><path d=\"M3 7l9 6l9 -6\"/></svg>",
            'footer' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M4 5h16\"/><path d=\"M4 19h16\"/><path d=\"M4 9h16v6h-16z\"/></svg>",
            'banners' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M4 6h16v12h-16z\"/><path d=\"M8 10h8\"/><path d=\"M8 14h5\"/></svg>",
            'seo' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0\"/><path d=\"M21 21l-6 -6\"/><path d=\"M7 10h6\"/></svg>",
            'sitemap' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M12 4m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0\"/><path d=\"M6 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0\"/><path d=\"M18 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0\"/><path d=\"M12 6v6h-6v4\"/><path d=\"M12 12h6v4\"/></svg>",
            'settings' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z\"/><path d=\"M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0\"/></svg>",
            'branding' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M3 21v-4a4 4 0 1 1 4 4z\"/><path d=\"M21 3a16 16 0 0 0 -12.8 10.2\"/><path d=\"M21 3a16 16 0 0 1 -10.2 12.8\"/><path d=\"M10.6 9a9 9 0 0 1 4.4 4.4\"/></svg>",
            'currency' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2\"/><path d=\"M12 3v3m0 12v3\"/></svg>",
            'whatsapp' => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M3 21l1.65 -3.8a9 9 0 1 1 3.4 3.05z\"/><path d=\"M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0z\"/><path d=\"M14 14.5c.5 .5 2 1 2.5 .5l.5 -.5a.5 .5 0 0 0 0 -.7l-1 -1a.5 .5 0 0 0 -.7 0l-.3 .3c-.5 -.2 -1.4 -.7 -2 -1.3s-1.1 -1.5 -1.3 -2l.3 -.3a.5 .5 0 0 0 0 -.7l-1 -1a.5 .5 0 0 0 -.7 0l-.5 .5c-.5 .5 0 2 1 3c1 1.5 2.2 2.7 3.2 3.2z\"/></svg>",
            default => "<svg {$attrs}><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><circle cx=\"12\" cy=\"12\" r=\"9\"/></svg>",
        };
    };
@endphp

<nav class="admin-sidebar-nav" aria-label="Admin navigation">
    <ul class="nav flex-column admin-sidebar-list">
        @foreach($menuGroups as $groupTitle => $items)
            <li class="admin-nav-section-title">{{ $groupTitle }}</li>
            @foreach($items as $item)
                @continue(! Route::has($item['route']))
                <li class="nav-item">
                    <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}">
                        <span class="nav-link-icon">{!! $renderIcon($item['icon']) !!}</span>
                        <span class="nav-link-title">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        @endforeach
    </ul>
</nav>
