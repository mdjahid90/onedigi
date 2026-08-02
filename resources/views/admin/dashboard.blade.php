@push('styles')
    <link rel="stylesheet" href="{{ (rtrim(request()->getBasePath(), '/') ?: '') }}/assets/vendor/apexcharts/apexcharts.css">
    <link rel="stylesheet" href="{{ (rtrim(request()->getBasePath(), '/') ?: '') }}/assets/vendor/jsvectormap/jsvectormap.min.css">
@endpush

<x-admin-layout>
    <x-slot name="header">
        <div class="page-header d-print-none" aria-label="Page header">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">Overview</div>
                        <h2 class="page-title">Dashboard</h2>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $financeSeries = collect($monthlyFinanceSeries);
        $transactionSeries = collect($monthlyTransactionStatistics);
        $invoiceSeries = collect($monthlyUnpaidInvoiceSeries);
        $customerSeries = collect($monthlyCustomerSeries);
        $orderSeries = collect($monthlyOrderSeries ?? []);
        $gatewaySeries = collect($gatewayStatistics);
        $trafficSeries = collect($dailyTrafficSeries ?? []);
        $locations = collect($locationSeries ?? []);
        $topProducts = collect($topProducts ?? []);
        $topProductsPreview = $topProducts->take(5);
        $realtime = $realtimeMetrics ?? [];

        $maxRevenue = max(1, (float) $financeSeries->max('revenue'));
        $maxProfit = max(1, (float) $financeSeries->max('profit'));
        $maxTransactions = max(1, (int) $transactionSeries->max('total'));
        $maxCustomers = max(1, (int) $customerSeries->max('total'));
        $maxInvoices = max(1, (int) $invoiceSeries->max('total'));
        $maxGateway = max(1, (int) $gatewaySeries->max('total'));
        $maxTraffic = max(1, (int) $trafficSeries->max('total'));
        $maxLocation = max(1, (int) $locations->max('total'));
        $maxTopProductRevenue = max(1, (float) $topProducts->max('revenue'));

        $newClientsThisMonth = (int) ($customerSeries->last()['total'] ?? 0);
        $ordersShipRate = $totalOrders > 0 ? ($ordersDelivered / max(1, $totalOrders)) * 100 : 0;
        $paymentPendingRate = $totalPayments > 0 ? ($pendingPayments / max(1, $totalPayments)) * 100 : 0;
        $conversionWidth = min(100, max(0, (float) $conversionRate));
        $revenueMonthRate = $totalRevenue > 0 ? min(100, ($revenueThisMonth / max(1, $totalRevenue)) * 100) : 0;
        $salesRate = min(100, max(0, $ordersShipRate));
        $conversionTrendClass = $conversionRateTrendPositive ? 'text-success' : 'text-danger';
        $conversionTrendSymbol = $conversionRateTrendPositive ? '+' : '';
    @endphp

    <div class="page-body" data-dashboard-realtime-url="{{ route('admin.analytics.realtime') }}">
        <div class="container-xl">
            <div class="row row-cards mb-3">
                <div class="col-lg-6">
                    <div class="card tabler-demo-welcome h-100">
                        <div class="card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-sm">
                                    <h2 class="tabler-demo-title">Welcome back,<br>Admin</h2>
                                    <p class="text-secondary mb-4">
                                        You have {{ number_format((int) $unreadAdminNotifications) }} unread notifications and {{ number_format((int) $openSupportTickets) }} open support tickets.
                                    </p>

                                    <div class="mb-4">
                                        <div class="subheader">Today's sales</div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="h3 mb-0">&#2547;{{ number_format((float) $revenueToday, 0) }}</div>
                                            <span class="text-success">{{ number_format((float) $revenueMonthRate, 0) }}%</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-success" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M3 17l6 -6l4 4l8 -8"/>
                                                <path d="M14 7h7v7"/>
                                            </svg>
                                        </div>
                                        <div class="progress progress-sm w-50 mt-2">
                                            <div class="progress-bar bg-success" style="width: {{ $revenueMonthRate }}%"></div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="subheader">Growth rate</div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="h3 mb-0">{{ number_format((float) $conversionRate, 1) }}%</div>
                                            <span class="{{ $conversionTrendClass }}">{{ $conversionTrendSymbol }}{{ number_format((float) $conversionRateDelta, 1) }} pp</span>
                                        </div>
                                        <div class="progress progress-sm w-50 mt-2">
                                            <div class="progress-bar {{ $conversionRateTrendPositive ? 'bg-success' : 'bg-danger' }}" style="width: {{ min(100, abs((float) $conversionRateDelta) * 8) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-auto">
                                    <div class="tabler-demo-illustration" aria-hidden="true">
                                        <div class="tabler-demo-blob">
                                            <div class="tabler-demo-check">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M5 12l5 5l10 -10"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="tabler-demo-person left"></div>
                                        <div class="tabler-demo-person right"></div>
                                        <div class="tabler-demo-machine"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card tabler-demo-chart-card h-100">
                        <div class="card-body tabler-demo-card-body">
                            <div class="subheader">Total users</div>
                            <div class="d-flex align-items-baseline gap-2">
                                <div class="h1 mb-0">{{ number_format((int) $totalUsers) }}</div>
                                <span class="text-success">{{ number_format((int) $newClientsThisMonth) }} new</span>
                            </div>
                            <div class="text-secondary mt-2">
                                {{ number_format((int) $visitorsThisMonth) }} visitors tracked this month
                                <span class="text-secondary">/</span>
                                <span data-realtime-views-today>{{ number_format((int) ($realtime['views_today'] ?? 0)) }}</span> views today
                            </div>
                        </div>
                        <div id="chart-total-users" class="tabler-apex-card-chart"></div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card tabler-demo-gauge-card h-100">
                        <div class="card-body tabler-demo-card-body">
                            <div class="subheader">Active users</div>
                            <div class="d-flex align-items-baseline gap-2">
                                <div class="h1 mb-0" data-realtime-active-users>{{ number_format((int) $activeUsers) }}</div>
                                <span class="{{ $activeUsers > 0 ? 'text-success' : 'text-secondary' }}">15 min</span>
                            </div>
                            <div id="chart-active-users-gauge" class="tabler-apex-gauge" data-realtime-active-gauge></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card tabler-demo-small-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="subheader">Sales</div>
                                <div class="text-secondary">This month</div>
                            </div>
                            <div class="h1 mb-3">{{ number_format((float) $salesRate, 0) }}%</div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Delivery rate</span>
                                <span class="text-success">{{ number_format((int) $ordersDelivered) }} shipped</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" style="width: {{ $salesRate }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card tabler-demo-small-card h-100">
                        <div class="card-body pb-0">
                            <div class="d-flex justify-content-between">
                                <div class="subheader">Revenue</div>
                                <div class="text-secondary">6 months</div>
                            </div>
                            <div class="d-flex align-items-baseline gap-2">
                                <div class="h1 mb-0">&#2547;{{ number_format((float) $revenueThisMonth, 0) }}</div>
                                <span class="text-success">{{ number_format((float) $revenueMonthRate, 0) }}%</span>
                            </div>
                        </div>
                        <div id="chart-revenue" class="tabler-apex-mini-chart"></div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card tabler-demo-small-card h-100">
                        <div class="card-body pb-0">
                            <div class="d-flex justify-content-between">
                                <div class="subheader">New clients</div>
                                <div class="text-secondary">6 months</div>
                            </div>
                            <div class="d-flex align-items-baseline gap-2">
                                <div class="h1 mb-0">{{ number_format((int) $newClientsThisMonth) }}</div>
                                <span class="text-secondary">current</span>
                            </div>
                        </div>
                        <div id="chart-new-clients" class="tabler-apex-mini-chart"></div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card tabler-demo-small-card h-100">
                        <div class="card-body pb-0">
                            <div class="d-flex justify-content-between">
                                <div class="subheader">Orders</div>
                                <div class="text-secondary">6 months</div>
                            </div>
                            <div class="d-flex align-items-baseline gap-2">
                                <div class="h1 mb-0">{{ number_format((int) $totalOrders) }}</div>
                                <span class="text-success">{{ number_format((int) $ordersDelivered) }} shipped</span>
                            </div>
                        </div>
                        <div id="chart-orders" class="tabler-apex-mini-chart"></div>
                    </div>
                </div>
            </div>

            <div class="row row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="avatar bg-primary text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 3v18"/>
                                    <path d="M17 7.5a4 4 0 0 0 -4 -2.5h-2a4 4 0 1 0 0 8h2a4 4 0 1 1 0 8h-2a4 4 0 0 1 -4 -2.5"/>
                                </svg>
                            </span>
                            <div>
                                <div>{{ number_format((int) $totalPayments) }} Payments</div>
                                <div class="text-secondary">{{ number_format((int) $pendingPayments) }} waiting payments</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="avatar bg-success text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M6 6h15l-1.5 9h-12z"/>
                                    <path d="M6 6l-2 -3"/>
                                    <path d="M9 20a1 1 0 1 0 0 2a1 1 0 0 0 0 -2"/>
                                    <path d="M18 20a1 1 0 1 0 0 2a1 1 0 0 0 0 -2"/>
                                </svg>
                            </span>
                            <div>
                                <div>{{ number_format((int) $totalOrders) }} Orders</div>
                                <div class="text-secondary">{{ number_format((int) $ordersDelivered) }} shipped</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="avatar bg-dark text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/>
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                                </svg>
                            </span>
                            <div>
                                <div>{{ number_format((int) $unreadContactMessages) }} Messages</div>
                                <div class="text-secondary">{{ number_format((int) $openSupportTickets) }} open tickets</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="avatar bg-info text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M4 19a2 2 0 0 1 2 -2h14"/>
                                    <path d="M6 2h14v20h-14a2 2 0 0 1 -2 -2v-16a2 2 0 0 1 2 -2"/>
                                </svg>
                            </span>
                            <div>
                                <div>{{ number_format((int) $activeSubscriptions) }} Subscriptions</div>
                                <div class="text-secondary">{{ number_format((int) $totalProducts) }} catalog products</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-cards">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Traffic summary</h3>
                        </div>
                        <div class="card-body">
                            <div id="chart-traffic-summary" class="tabler-apex-large-chart" data-realtime-traffic></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Locations</h3>
                        </div>
                        <div class="card-body">
                            <div id="dashboard-world-map" class="tabler-dashboard-map" data-realtime-locations></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Top selling products</h3>
                            @if($topProducts->isNotEmpty())
                                <div class="card-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-top-selling-products-open>
                                        More list
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="list-group list-group-flush">
                            @forelse($topProductsPreview as $product)
                                @php
                                    $productRevenue = (float) ($product['revenue'] ?? 0);
                                    $productPct = min(100, max(6, ($productRevenue / $maxTopProductRevenue) * 100));
                                    $growth = (float) ($product['growth_pct'] ?? 0);
                                    $productImage = !empty($product['image_path']) ? Storage::url($product['image_path']) : null;
                                @endphp
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($productImage)
                                            <span class="avatar top-product-avatar" style="background-image: url('{{ $productImage }}')"></span>
                                        @else
                                            <span class="avatar bg-primary-lt text-primary">{{ $loop->iteration }}</span>
                                        @endif
                                        <div class="min-w-0 flex-fill">
                                            <div class="d-flex align-items-center justify-content-between gap-3">
                                                <div class="text-truncate fw-semibold">
                                                    @if(!empty($product['product_slug']))
                                                        <a href="{{ route('products.show', $product['product_slug']) }}" target="_blank" class="text-reset">{{ $product['title'] }}</a>
                                                    @else
                                                        {{ $product['title'] }}
                                                    @endif
                                                </div>
                                                <div class="fw-semibold text-nowrap">&#2547;{{ number_format($productRevenue, 0) }}</div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between gap-3 mt-1">
                                                <div class="text-secondary small">{{ number_format((int) ($product['sales_count'] ?? 0)) }} sold this month</div>
                                                <span class="badge {{ $growth >= 0 ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                                    {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 0) }}%
                                                </span>
                                            </div>
                                            <div class="progress progress-sm mt-2">
                                                <div class="progress-bar bg-primary" style="width: {{ $productPct }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty py-5">
                                    <p class="empty-title">No product sales yet</p>
                                    <p class="empty-subtitle text-secondary">Delivered order items will appear here.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Gateway usage</h3>
                        </div>
                        <div class="card-body">
                            @forelse($gatewaySeries as $gateway)
                                @php
                                    $pct = min(100, max(6, (((int) $gateway['total']) / $maxGateway) * 100));
                                @endphp
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold">{{ $gateway['gateway'] }}</span>
                                        <span class="text-secondary">{{ number_format((int) $gateway['total']) }}</span>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty">
                                    <p class="empty-title">No gateway data</p>
                                    <p class="empty-subtitle text-secondary">Payment gateway activity will appear here.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Development activity</h3>
                        </div>
                        <div class="card-body pb-0">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="tabler-demo-ring" style="--ring-value: {{ $revenueMonthRate * 3.6 }}deg"></div>
                                <div>
                                    <div>Today's earning: &#2547;{{ number_format((float) $revenueToday, 2) }}</div>
                                    <div class="text-success">{{ number_format((float) $revenueMonthRate, 0) }}% of total revenue</div>
                                </div>
                            </div>
                        </div>
                        <div id="chart-development-activity" class="tabler-apex-activity-chart"></div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Orders</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                        <tr>
                                            <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">#{{ $order->id }}</a></td>
                                            <td>
                                                <div class="fw-semibold">{{ $order->customer_name }}</div>
                                                <div class="text-secondary">{{ $order->customer_email }}</div>
                                            </td>
                                            <td>
                                                <span class="@if($order->status === 'DELIVERED') text-success @elseif($order->status === 'PROCESSING') text-warning @elseif($order->status === 'CANCELLED') text-danger @else text-secondary @endif">
                                                    &#9679; {{ ucfirst(strtolower($order->status)) }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-semibold">&#2547;{{ number_format((float) $order->total_amount, 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-secondary py-5">No recent orders found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="top-products-modal" id="topSellingProductsModal" tabindex="-1" aria-labelledby="topSellingProductsModalLabel" aria-hidden="true">
        <div class="top-products-modal__backdrop" data-top-selling-products-close></div>
        <div class="top-products-modal__dialog">
            <div class="card top-products-modal__content">
                <div class="card-header">
                    <div>
                        <h5 class="modal-title" id="topSellingProductsModalLabel">Top selling products</h5>
                        <div class="text-secondary small">Sorted by highest sales revenue this month.</div>
                    </div>
                    <button type="button" class="btn-close" data-top-selling-products-close aria-label="Close"></button>
                </div>
                <div class="top-products-modal__body">
                    <div class="list-group list-group-flush">
                        @forelse($topProducts as $product)
                            @php
                                $productRevenue = (float) ($product['revenue'] ?? 0);
                                $productPct = min(100, max(6, ($productRevenue / $maxTopProductRevenue) * 100));
                                $growth = (float) ($product['growth_pct'] ?? 0);
                                $productImage = !empty($product['image_path']) ? Storage::url($product['image_path']) : null;
                            @endphp
                            <div class="list-group-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-secondary fw-semibold top-product-rank">{{ $loop->iteration }}</div>
                                    @if($productImage)
                                        <span class="avatar top-product-avatar" style="background-image: url('{{ $productImage }}')"></span>
                                    @else
                                        <span class="avatar bg-primary-lt text-primary">{{ \Illuminate\Support\Str::substr((string) ($product['title'] ?? 'P'), 0, 1) }}</span>
                                    @endif
                                    <div class="min-w-0 flex-fill">
                                        <div class="d-flex align-items-center justify-content-between gap-3">
                                            <div class="text-truncate fw-semibold">
                                                @if(!empty($product['product_slug']))
                                                    <a href="{{ route('products.show', $product['product_slug']) }}" target="_blank" class="text-reset">{{ $product['title'] }}</a>
                                                @else
                                                    {{ $product['title'] }}
                                                @endif
                                            </div>
                                            <div class="fw-semibold text-nowrap">&#2547;{{ number_format($productRevenue, 0) }}</div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between gap-3 mt-1">
                                            <div class="text-secondary small">{{ number_format((int) ($product['sales_count'] ?? 0)) }} sold this month</div>
                                            <span class="badge {{ $growth >= 0 ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                                {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 0) }}%
                                            </span>
                                        </div>
                                        <div class="progress progress-sm mt-2">
                                            <div class="progress-bar bg-primary" style="width: {{ $productPct }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty py-5">
                                <p class="empty-title">No product sales yet</p>
                                <p class="empty-subtitle text-secondary">Delivered order items will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ (rtrim(request()->getBasePath(), '/') ?: '') }}/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="{{ (rtrim(request()->getBasePath(), '/') ?: '') }}/assets/vendor/jsvectormap/jsvectormap.min.js"></script>
    <script src="{{ (rtrim(request()->getBasePath(), '/') ?: '') }}/assets/vendor/jsvectormap/maps/world.js"></script>
    <script>
        (() => {
            const root = document.querySelector('[data-dashboard-realtime-url]');
            if (!root || !window.ApexCharts) return;

            const numberFormat = new Intl.NumberFormat();
            const moneyFormat = new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 });
            const chartData = {
                finance: @json($financeSeries->values()->all()),
                customers: @json($customerSeries->values()->all()),
                invoices: @json($invoiceSeries->values()->all()),
                orders: @json($orderSeries->values()->all()),
                traffic: @json($trafficSeries->values()->all()),
                locations: @json($locations->values()->all()),
                activeUsersPercent: {{ (int) round($totalUsers > 0 ? (($activeUsers / max(1, $totalUsers)) * 100) : 0) }},
            };

            const blue = '#0b75d1';
            const green = '#2fb344';
            const muted = '#98a6b3';
            const grid = '#edf1f5';
            const commonTooltip = { theme: 'dark', marker: { show: true }, style: { fontSize: '12px' } };
            const miniTooltip = { ...commonTooltip, shared: false, intersect: false, fixed: { enabled: true, position: 'topRight', offsetX: -8, offsetY: 4 } };
            const chartRefs = {};
            const labels = (rows) => rows.map((row) => row.label);
            const values = (rows, key) => rows.map((row) => Number(row[key] || 0));

            const renderChart = (selector, options) => {
                const element = document.querySelector(selector);
                if (!element) return null;
                const chart = new ApexCharts(element, options);
                chart.render();
                return chart;
            };

            chartRefs.totalUsers = renderChart('#chart-total-users', {
                chart: { type: 'line', height: 132, sparkline: { enabled: true }, animations: { enabled: true } },
                series: [
                    { name: 'Users', data: values(chartData.customers, 'total') },
                    { name: 'Visitors', data: values(chartData.traffic.slice(-chartData.customers.length), 'total') },
                ],
                colors: [blue, muted],
                stroke: { curve: 'smooth', width: [3, 2], dashArray: [0, 4] },
                markers: { size: 0, hover: { size: 5 } },
                tooltip: { ...miniTooltip, x: { formatter: (_, opts) => labels(chartData.customers)[opts.dataPointIndex] || '' } },
            });

            chartRefs.activeGauge = renderChart('#chart-active-users-gauge', {
                chart: { type: 'radialBar', width: 190, height: 154, sparkline: { enabled: true } },
                series: [chartData.activeUsersPercent],
                colors: [blue],
                plotOptions: {
                    radialBar: {
                        startAngle: -115,
                        endAngle: 115,
                        hollow: { size: '58%' },
                        track: { background: '#e6e9ee', strokeWidth: '95%' },
                        dataLabels: {
                            name: { show: false },
                            value: { offsetY: 4, fontSize: '24px', fontWeight: 500, formatter: (value) => `${Math.round(value)}%` },
                        },
                    },
                },
            });

            chartRefs.revenue = renderChart('#chart-revenue', {
                chart: { type: 'area', height: 76, sparkline: { enabled: true } },
                series: [{ name: 'Revenue', data: values(chartData.finance, 'revenue') }],
                colors: [blue],
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { shadeIntensity: .2, opacityFrom: .28, opacityTo: .05 } },
                markers: { size: 0, hover: { size: 5 } },
                tooltip: {
                    ...miniTooltip,
                    x: { formatter: (_, opts) => labels(chartData.finance)[opts.dataPointIndex] || '' },
                    y: { formatter: (value) => `\u09f3${moneyFormat.format(value)}` },
                },
            });

            chartRefs.newClients = renderChart('#chart-new-clients', {
                chart: { type: 'line', height: 76, sparkline: { enabled: true } },
                series: [
                    { name: 'Clients', data: values(chartData.customers, 'total') },
                    { name: 'Previous', data: values(chartData.customers, 'total').map((value, index) => Math.max(0, value - index - 1)) },
                ],
                colors: [blue, muted],
                stroke: { curve: 'smooth', width: [2, 1.5], dashArray: [0, 4] },
                markers: { size: 0, hover: { size: 5 } },
                tooltip: { ...miniTooltip, x: { formatter: (_, opts) => labels(chartData.customers)[opts.dataPointIndex] || '' } },
            });

            chartRefs.orders = renderChart('#chart-orders', {
                chart: { type: 'bar', height: 76, sparkline: { enabled: true } },
                series: [
                    { name: 'Orders', data: values(chartData.orders, 'total') },
                    { name: 'Shipped', data: values(chartData.orders, 'delivered') },
                ],
                colors: [blue, green],
                plotOptions: { bar: { columnWidth: '45%', borderRadius: 0 } },
                tooltip: { ...miniTooltip, x: { formatter: (_, opts) => labels(chartData.orders)[opts.dataPointIndex] || '' } },
            });

            chartRefs.traffic = renderChart('#chart-traffic-summary', {
                chart: { type: 'bar', height: 240, stacked: true, toolbar: { show: false }, animations: { enabled: true } },
                series: [
                    { name: 'Web', data: values(chartData.traffic, 'web') },
                    { name: 'Mobile', data: values(chartData.traffic, 'mobile') },
                ],
                colors: [blue, green],
                plotOptions: { bar: { columnWidth: '45%', borderRadius: 0 } },
                dataLabels: { enabled: false },
                grid: { borderColor: grid, strokeDashArray: 4 },
                xaxis: {
                    categories: labels(chartData.traffic),
                    labels: { rotate: 0, style: { colors: '#526070', fontSize: '12px' }, hideOverlappingLabels: true },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: { labels: { style: { colors: '#526070' } } },
                legend: { show: false },
                tooltip: commonTooltip,
            });

            chartRefs.activity = renderChart('#chart-development-activity', {
                chart: { type: 'area', height: 220, toolbar: { show: false }, sparkline: { enabled: false } },
                series: [{ name: 'Purchases', data: values(chartData.finance, 'revenue') }],
                colors: [blue],
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'solid', opacity: .18 },
                markers: { size: 0, hover: { size: 6 } },
                dataLabels: { enabled: false },
                grid: { borderColor: grid, strokeDashArray: 4, xaxis: { lines: { show: false } } },
                xaxis: { categories: labels(chartData.finance), labels: { style: { colors: '#526070' } } },
                yaxis: { show: false },
                tooltip: { ...commonTooltip, y: { formatter: (value) => `\u09f3${moneyFormat.format(value)}` } },
            });

            const locationValues = (locations) => locations.reduce((carry, row) => {
                if (row.code) carry[String(row.code).toUpperCase()] = Number(row.total || 0);
                return carry;
            }, {});

            let map;
            const mapElement = document.querySelector('#dashboard-world-map');
            if (mapElement && window.jsVectorMap) {
                map = new jsVectorMap({
                    selector: '#dashboard-world-map',
                    map: 'world',
                    zoomButtons: false,
                    zoomOnScroll: false,
                    regionStyle: {
                        initial: { fill: '#dbeaf7', stroke: '#ffffff', strokeWidth: .7 },
                        hover: { fill: blue, cursor: 'pointer' },
                        selected: { fill: blue },
                    },
                    series: {
                        regions: [{
                            attribute: 'fill',
                            scale: ['#dbeaf7', blue],
                            values: locationValues(chartData.locations),
                        }],
                    },
                    onRegionTooltipShow(event, tooltip, code) {
                        const row = chartData.locations.find((item) => String(item.code || '').toUpperCase() === code);
                        tooltip.text(row ? `${row.label}: ${numberFormat.format(row.total || 0)}` : tooltip.text());
                    },
                });
            }

            const activeUsers = document.querySelector('[data-realtime-active-users]');
            const viewsToday = document.querySelector('[data-realtime-views-today]');
            const refreshUrl = root.getAttribute('data-dashboard-realtime-url');

            const refresh = async () => {
                try {
                    const response = await fetch(refreshUrl, {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) return;
                    const data = await response.json();

                    if (activeUsers) activeUsers.textContent = numberFormat.format(data.active_users || 0);
                    if (viewsToday) viewsToday.textContent = numberFormat.format(data.views_today || 0);
                    chartRefs.activeGauge?.updateSeries([Number(data.active_users_percent || 0)]);

                    if (Array.isArray(data.traffic)) {
                        chartData.traffic = data.traffic;
                        chartRefs.traffic?.updateOptions({ xaxis: { categories: labels(data.traffic) } }, false, false);
                        chartRefs.traffic?.updateSeries([
                            { name: 'Web', data: values(data.traffic, 'web') },
                            { name: 'Mobile', data: values(data.traffic, 'mobile') },
                        ]);
                    }

                    if (Array.isArray(data.locations)) {
                        chartData.locations = data.locations;
                        map?.series?.regions?.[0]?.setValues(locationValues(data.locations));
                    }
                } catch (error) {
                    console.debug('Dashboard realtime charts refresh failed', error);
                }
            };

            window.setInterval(refresh, 15000);
            refresh();

            const topSellingModal = document.getElementById('topSellingProductsModal');
            const topSellingButton = document.querySelector('[data-top-selling-products-open]');

            if (topSellingModal && topSellingButton) {
                const closeModal = () => {
                    topSellingModal.classList.remove('is-open');
                    topSellingModal.setAttribute('aria-hidden', 'true');
                    topSellingModal.removeAttribute('aria-modal');
                    topSellingModal.removeAttribute('role');
                    document.body.classList.remove('top-products-modal-open');
                    document.body.style.removeProperty('overflow');
                };

                const openModal = (event) => {
                    event.preventDefault();
                    topSellingModal.removeAttribute('aria-hidden');
                    topSellingModal.setAttribute('aria-modal', 'true');
                    topSellingModal.setAttribute('role', 'dialog');
                    topSellingModal.classList.add('is-open');
                    document.body.classList.add('top-products-modal-open');
                    document.body.style.overflow = 'hidden';
                    topSellingModal.querySelector('[data-top-selling-products-close]')?.focus();
                };

                topSellingButton.addEventListener('click', openModal);
                topSellingModal.querySelectorAll('[data-top-selling-products-close]').forEach((button) => {
                    button.addEventListener('click', closeModal);
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && topSellingModal.classList.contains('show')) {
                        closeModal();
                    }
                });
            }
        })();
    </script>
</x-admin-layout>
