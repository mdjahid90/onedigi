@push('styles')
    <link rel="stylesheet" href="{{ (rtrim(request()->getBasePath(), '/') ?: '') }}/assets/vendor/apexcharts/apexcharts.css">
    <style>
        .analytics-top-products-card {
            overflow: visible;
        }

        .analytics-product-list {
            display: grid;
            gap: 0;
        }

        .analytics-product-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(210px, 240px);
            gap: 1.5rem;
            align-items: center;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--tblr-border-color);
            background: var(--tblr-bg-surface);
        }

        .analytics-product-row:first-child {
            border-top: 0;
        }

        .analytics-product-media {
            width: 56px;
            height: 56px;
            object-fit: cover;
        }

        .analytics-product-title {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .analytics-product-meta {
            min-width: 0;
        }

        .analytics-product-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .5rem;
            flex-shrink: 0;
        }

        .analytics-product-actions .btn {
            width: 2rem;
            height: 2rem;
            color: var(--tblr-secondary);
            background: var(--tblr-bg-surface-secondary);
            border: 1px solid var(--tblr-border-color);
            opacity: 1;
        }

        .analytics-product-actions .btn:hover,
        .analytics-product-actions .btn.show {
            color: var(--tblr-primary);
            border-color: var(--tblr-primary);
            background: var(--tblr-primary-lt);
        }

        .analytics-product-progress {
            padding-right: 2.75rem;
        }

        .analytics-top-products-card .dropdown-menu {
            z-index: 1080;
        }

        @media (max-width: 575.98px) {
            .analytics-product-row {
                grid-template-columns: 1fr;
            }

            .analytics-product-meta {
                min-width: 0;
                width: 100%;
            }

            .analytics-product-progress {
                padding-right: 0;
            }
        }

        @media (min-width: 992px) {
            .analytics-top-products-card {
                height: auto;
            }
        }
    </style>
@endpush

<x-admin-layout>
    <x-slot name="header">
        <div class="page-header d-print-none" aria-label="Page header">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">Reports</div>
                        <h2 class="page-title">Analytics</h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <form method="GET" action="{{ route('admin.analytics.index') }}" class="analytics-filter-bar">
                            <select class="form-select" name="period" data-analytics-period>
                                @foreach($periodOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $period === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                                <option value="custom" {{ $isCustom ? 'selected' : '' }}>Custom range</option>
                            </select>
                            <input type="date" name="start" value="{{ $start->toDateString() }}" class="form-control" data-analytics-custom>
                            <input type="date" name="end" value="{{ $end->toDateString() }}" class="form-control" data-analytics-custom>
                            <button class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M4 6h16"/>
                                    <path d="M7 12h10"/>
                                    <path d="M10 18h4"/>
                                </svg>
                                Apply
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $viewsTrend = ($metrics['previous_views'] ?? 0) > 0 ? (($metrics['views'] - $metrics['previous_views']) / max(1, $metrics['previous_views'])) * 100 : (($metrics['views'] ?? 0) > 0 ? 100 : 0);
        $visitorsTrend = ($metrics['previous_visitors'] ?? 0) > 0 ? (($metrics['visitors'] - $metrics['previous_visitors']) / max(1, $metrics['previous_visitors'])) * 100 : (($metrics['visitors'] ?? 0) > 0 ? 100 : 0);
        $ordersTrend = ($metrics['previous_orders'] ?? 0) > 0 ? (($metrics['orders'] - $metrics['previous_orders']) / max(1, $metrics['previous_orders'])) * 100 : (($metrics['orders'] ?? 0) > 0 ? 100 : 0);
        $conversionDelta = ($metrics['conversion_rate'] ?? 0) - ($metrics['previous_conversion_rate'] ?? 0);
        $successDelta = ($metrics['success_rate'] ?? 0) - ($metrics['previous_success_rate'] ?? 0);
        $metricCards = [
            ['label' => 'Page views', 'value' => number_format((int) $metrics['views']), 'meta' => number_format($viewsTrend, 1) . '% vs previous', 'positive' => $viewsTrend >= 0, 'color' => 'primary'],
            ['label' => 'Visitors', 'value' => number_format((int) $metrics['visitors']), 'meta' => number_format($visitorsTrend, 1) . '% vs previous', 'positive' => $visitorsTrend >= 0, 'color' => 'info'],
            ['label' => 'Orders', 'value' => number_format((int) $metrics['orders']), 'meta' => number_format($ordersTrend, 1) . '% vs previous', 'positive' => $ordersTrend >= 0, 'color' => 'success'],
            ['label' => 'Revenue', 'value' => '৳' . number_format((float) $metrics['revenue'], 0), 'meta' => number_format((int) $metrics['delivered_orders']) . ' delivered', 'positive' => true, 'color' => 'purple'],
            ['label' => 'Conversion', 'value' => number_format((float) $metrics['conversion_rate'], 2) . '%', 'meta' => ($conversionDelta >= 0 ? '+' : '') . number_format($conversionDelta, 2) . ' pp', 'positive' => $conversionDelta >= 0, 'color' => 'warning'],
            ['label' => 'Payment success', 'value' => number_format((float) $metrics['success_rate'], 2) . '%', 'meta' => ($successDelta >= 0 ? '+' : '') . number_format($successDelta, 2) . ' pp', 'positive' => $successDelta >= 0, 'color' => 'teal'],
        ];
    @endphp

    <div class="page-body">
        <div class="container-xl">
            @unless($eventsReady)
                <div class="alert alert-warning mb-3">
                    Analytics event table is not available. Sales data will still work, but visitor, device, source and location reports need tracking data.
                </div>
            @endunless

            <div class="row row-cards mb-3">
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="page-pretitle">Selected range</div>
                                    <h2 class="page-title mb-2">{{ $dateRange }}</h2>
                                    <div class="text-secondary">Compared with {{ $previousStart->format('M d, Y') }} - {{ $previousEnd->format('M d, Y') }}</div>
                                </div>
                                <div class="badge {{ $isCustom ? 'bg-primary-lt' : 'bg-secondary-lt' }}">{{ $isCustom ? 'Custom' : ($periodOptions[$period] ?? 'Preset') }}</div>
                            </div>
                            <div id="analytics-overview-chart" class="analytics-main-chart mt-3"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Business health</h3>
                        </div>
                        <div class="card-body">
                            <div class="analytics-health-meter" style="--analytics-health: {{ min(100, max(0, (float) $metrics['conversion_rate'] * 10)) * 3.6 }}deg">
                                <span>{{ number_format((float) $metrics['conversion_rate'], 1) }}%</span>
                            </div>
                            <div class="text-center text-secondary mt-3">Visitor to order conversion</div>
                            <div class="row g-3 mt-2">
                                <div class="col-6">
                                    <div class="subheader">Average order</div>
                                    <div class="h3 mb-0">৳{{ number_format((float) $metrics['average_order'], 0) }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="subheader">Transactions</div>
                                    <div class="h3 mb-0">{{ number_format((int) $metrics['transactions']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-cards mb-3">
                @foreach($metricCards as $card)
                    <div class="col-sm-6 col-xl-2">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="subheader">{{ $card['label'] }}</div>
                                <div class="h2 mb-2">{{ $card['value'] }}</div>
                                <div class="{{ $card['positive'] ? 'text-success' : 'text-danger' }}">{{ $card['meta'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row row-cards mb-3">
                <div class="col-lg-8">
                    <div class="card w-100">
                        <div class="card-body">
                            <div class="d-sm-flex d-block align-items-center justify-content-between mb-4">
                                <div class="mb-3 mb-sm-0">
                                    <h3 class="card-title fw-semibold mb-0">Sales Profit</h3>
                                </div>
                                <select class="form-select form-select-sm w-auto" data-analytics-card-period>
                                    @foreach($periodOptions as $value => $label)
                                        <option value="{{ route('admin.analytics.index', ['period' => $value]) }}" {{ $period === $value && ! $isCustom ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                    <option value="{{ route('admin.analytics.index', ['period' => 'custom', 'start' => $start->toDateString(), 'end' => $end->toDateString()]) }}" {{ $isCustom ? 'selected' : '' }}>Custom range</option>
                                </select>
                            </div>
                            <div id="sales-profit" class="analytics-chart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Traffic sources</h3>
                        </div>
                        <div class="card-body">
                            <div id="analytics-source-chart" class="analytics-donut"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-cards mb-3">
                <div class="col-md-6 col-xl-3">
                    @include('admin.analytics.partials.dimension-card', ['title' => 'Devices', 'rows' => $devices])
                </div>
                <div class="col-md-6 col-xl-3">
                    @include('admin.analytics.partials.dimension-card', ['title' => 'Browsers', 'rows' => $browsers])
                </div>
                <div class="col-md-6 col-xl-3">
                    @include('admin.analytics.partials.dimension-card', ['title' => 'Countries', 'rows' => $countries])
                </div>
                <div class="col-md-6 col-xl-3">
                    @include('admin.analytics.partials.dimension-card', ['title' => 'Event types', 'rows' => $eventTypes])
                </div>
            </div>

            <div class="row row-cards">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Top pages</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Page</th>
                                        <th class="text-end">Views</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topPages as $row)
                                        <tr class="{{ $loop->iteration > 8 ? 'd-none' : '' }}" data-load-more-item="top-pages">
                                            <td class="text-truncate" style="max-width: 360px;">{{ $row['label'] }}</td>
                                            <td class="text-end fw-semibold">{{ number_format((int) $row['total']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-center text-secondary py-4">No page data in this range.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($topPages->count() > 8)
                            <div class="card-footer text-center">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-load-more="top-pages" data-load-step="8">
                                    Load more <span class="text-secondary ms-1">({{ $topPages->count() - 8 }} more)</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6 align-self-start">
                    <div class="card analytics-top-products-card">
                        <div class="card-body pb-0">
                            <h3 class="card-title mb-1">Top products</h3>
                            <p class="text-secondary mb-0">{{ number_format($topProducts->sum('sold')) }} sold in selected range</p>
                        </div>
                        <div class="analytics-product-list">
                            @forelse($topProducts as $index => $product)
                                <div class="analytics-product-row {{ $loop->iteration > 5 ? 'd-none' : '' }}" data-load-more-item="top-products">
                                    <div class="d-flex align-items-center min-w-0">
                                        <img src="{{ $product['image_url'] }}" class="analytics-product-media flex-shrink-0 rounded border" alt="{{ $product['title'] }}">
                                        <div class="ms-3 min-w-0">
                                            <div class="fw-semibold analytics-product-title">
                                                @if($product['slug'])
                                                    <a href="{{ route('products.show', $product['slug']) }}" target="_blank" class="text-reset">{{ $product['title'] }}</a>
                                                @else
                                                    {{ $product['title'] }}
                                                @endif
                                            </div>
                                            <div class="text-secondary small">{{ number_format((int) $product['sold']) }} sold</div>
                                        </div>
                                    </div>
                                    <div class="analytics-product-meta">
                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                            <div>
                                                <div class="fw-semibold">&#2547;{{ number_format((float) $product['revenue'], 0) }} <span class="text-secondary fw-normal">/{{ number_format((int) $product['sold']) }}</span></div>
                                                <div class="text-secondary small">Revenue / sold</div>
                                            </div>
                                            <div class="analytics-product-actions">
                                                <span class="badge rounded-pill {{ $product['is_active'] ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                                                    {{ $product['is_active'] ? 'Active' : 'Archived' }}
                                                </span>
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-icon btn-ghost-secondary btn-sm" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false" aria-label="Product actions">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-dots-vertical" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                            <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                                                            <path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                                                            <path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                                                        </svg>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        @if($product['slug'])
                                                            <a class="dropdown-item" href="{{ route('products.show', $product['slug']) }}" target="_blank">View</a>
                                                            <a class="dropdown-item" href="{{ route('admin.products.edit', $product['slug']) }}">Edit</a>
                                                        @else
                                                            <span class="dropdown-item text-secondary">No product link</span>
                                                        @endif
                                                        <a class="dropdown-item" href="{{ route('admin.products.index') }}">Products</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="analytics-product-progress">
                                            <div class="progress bg-secondary-lt flex-fill" style="height: 4px">
                                                <div class="progress-bar {{ $index === 0 ? 'bg-success' : 'bg-warning' }}" role="progressbar"
                                                    style="width: {{ number_format((float) $product['progress'], 0) }}%" aria-valuenow="{{ number_format((float) $product['progress'], 0) }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <div class="fw-semibold mb-1">No product sales yet</div>
                                    <div class="text-secondary">Real top products will appear after orders are created in this range.</div>
                                </div>
                            @endforelse
                        </div>
                        @if($topProducts->count() > 5)
                            <div class="card-footer text-center">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-load-more="top-products" data-load-step="5">
                                    Load more <span class="text-secondary ms-1">({{ $topProducts->count() - 5 }} more)</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ (rtrim(request()->getBasePath(), '/') ?: '') }}/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script>
        (() => {
            const periodSelect = document.querySelector('[data-analytics-period]');
            const customInputs = document.querySelectorAll('[data-analytics-custom]');
            const updateCustomState = () => {
                const custom = periodSelect?.value === 'custom';
                customInputs.forEach((input) => input.toggleAttribute('required', custom));
            };
            periodSelect?.addEventListener('change', () => {
                updateCustomState();
                if (periodSelect.value !== 'custom') {
                    periodSelect.form?.submit();
                }
            });
            updateCustomState();

            document.querySelectorAll('[data-load-more]').forEach((button) => {
                const target = button.getAttribute('data-load-more');
                const step = Number(button.getAttribute('data-load-step') || 8);
                const rows = Array.from(document.querySelectorAll(`[data-load-more-item="${target}"]`));

                const syncButton = () => {
                    const hidden = rows.filter((row) => row.classList.contains('d-none')).length;
                    const count = button.querySelector('span');

                    if (hidden <= 0) {
                        button.closest('.card-footer')?.classList.add('d-none');
                        return;
                    }

                    if (count) {
                        count.textContent = `(${hidden} more)`;
                    }
                };

                button.addEventListener('click', () => {
                    rows
                        .filter((row) => row.classList.contains('d-none'))
                        .slice(0, step)
                        .forEach((row) => row.classList.remove('d-none'));
                    syncButton();
                });

                syncButton();
            });

            if (!window.ApexCharts) return;

            const series = @json($series);
            const labels = series.map((row) => row.label);
            const values = (key) => series.map((row) => Number(row[key] || 0));
            document.querySelector('[data-analytics-card-period]')?.addEventListener('change', (event) => {
                if (event.target.value) {
                    window.location.href = event.target.value;
                }
            });
            const blue = '#0b75d1';
            const green = '#2fb344';
            const purple = '#5f38f9';
            const orange = '#f59f00';
            const grid = '#edf1f5';
            const revenueValues = values('revenue');
            const orderValues = values('orders');
            const transactionValues = values('transactions');
            const hasRevenue = revenueValues.some((value) => value > 0);
            const primaryValues = hasRevenue ? revenueValues : orderValues;
            const secondaryValues = hasRevenue ? orderValues : transactionValues;
            const primaryName = hasRevenue ? 'Revenue' : 'Orders';
            const secondaryName = hasRevenue ? 'Orders' : 'Transactions';
            const maxSalesProfit = Math.max(1, ...primaryValues, ...secondaryValues);

            new ApexCharts(document.querySelector('#analytics-overview-chart'), {
                chart: { type: 'area', height: 280, toolbar: { show: false } },
                series: [
                    { name: 'Views', data: values('views') },
                    { name: 'Orders', data: values('orders') },
                ],
                colors: [blue, green],
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: .22, opacityTo: .05 } },
                dataLabels: {
                    enabled: false,
                },
                grid: { borderColor: grid, strokeDashArray: 4 },
                xaxis: { categories: labels, labels: { rotate: -25, style: { colors: '#667382' } } },
                yaxis: { labels: { style: { colors: '#667382' } } },
                tooltip: { theme: 'dark' },
            }).render();

            new ApexCharts(document.querySelector('#sales-profit'), {
                series: [
                    {
                        type: 'area',
                        name: primaryName,
                        data: series.map((row, index) => ({ x: row.label, y: primaryValues[index] || 0 })),
                    },
                    {
                        type: 'line',
                        name: secondaryName,
                        data: series.map((row, index) => ({ x: row.label, y: secondaryValues[index] || 0 })),
                    },
                ],
                chart: {
                    height: 300,
                    fontFamily: 'inherit',
                    foreColor: '#111c2d99',
                    offsetX: -15,
                    offsetY: 10,
                    animations: { speed: 500 },
                    toolbar: { show: false },
                },
                colors: ['#0d8cff', '#6c757d'],
                dataLabels: { enabled: false },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 0,
                        inverseColors: false,
                        opacityFrom: 0.12,
                        opacityTo: 0,
                        stops: [100],
                    },
                },
                grid: {
                    show: true,
                    strokeDashArray: 3,
                    borderColor: '#90A4AE50',
                },
                stroke: {
                    curve: 'smooth',
                    width: 2,
                },
                xaxis: {
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    tickAmount: Math.min(labels.length, 8),
                    labels: {
                        rotate: 0,
                        hideOverlappingLabels: true,
                        trim: true,
                        style: { colors: '#8a93a2', fontSize: '12px' },
                    },
                },
                yaxis: {
                    min: 0,
                    max: Math.ceil(maxSalesProfit * 1.2),
                    tickAmount: 3,
                    labels: {
                        formatter: (value) => hasRevenue
                            ? '৳' + Number(value || 0).toLocaleString()
                            : Number(value || 0).toLocaleString(),
                    },
                },
                legend: { show: false },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: (value, { seriesIndex }) => {
                            if (hasRevenue && seriesIndex === 0) {
                                return '৳' + Number(value || 0).toLocaleString();
                            }

                            return Number(value || 0).toLocaleString();
                        },
                    },
                },
            }).render();

            const sourceRows = @json($sources->values()->all());
            new ApexCharts(document.querySelector('#analytics-source-chart'), {
                chart: { type: 'donut', height: 260 },
                series: sourceRows.map((row) => Number(row.total || 0)),
                labels: sourceRows.map((row) => row.label),
                colors: [blue, green, purple, orange, '#dc3545'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: false },
                tooltip: { theme: 'dark' },
            }).render();
        })();
    </script>
</x-admin-layout>
