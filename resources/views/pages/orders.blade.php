<x-user-dashboard-layout title="My Orders" pretitle="Orders">
    <div class="orders-page-stack">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
            <div class="fw-semibold text-dark">{{ __('ui.my_orders') }}</div>
            <div class="btn-list">
                <a href="{{ route('downloads.index') }}" class="btn btn-outline-secondary btn-sm">Download Center</a>
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm">{{ __('ui.browse_products') }}</a>
            </div>
        </div>

        <div class="card mt-3 d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-vcenter card-table text-nowrap">
                    <thead>
                        <tr>
                            <th>{{ __('ui.order') }}</th>
                            <th>{{ __('ui.status') }}</th>
                            <th>{{ __('ui.amount') }}</th>
                            <th>{{ __('ui.date') }}</th>
                            <th class="text-end">{{ __('ui.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="fw-semibold text-dark">#{{ $order->id }}</td>
                                <td>
                                    <span class="badge {{ $order->status === 'DELIVERED' ? 'bg-success-lt' : ($order->status === 'CANCELLED' ? 'bg-danger-lt' : 'bg-warning-lt') }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td><x-money :amount="(float) $order->total_amount" /></td>
                                <td class="text-secondary">{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-primary btn-sm">{{ __('ui.view') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-secondary text-center py-5" colspan="5">{{ __('ui.no_orders_yet') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-md-none mt-3">
            <div class="vstack gap-3">
                @forelse($orders as $order)
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="text-secondary small text-uppercase fw-semibold">{{ __('ui.order') }}</div>
                                    <div class="h3 m-0">#{{ $order->id }}</div>
                                </div>
                                <span class="badge {{ $order->status === 'DELIVERED' ? 'bg-success-lt' : ($order->status === 'CANCELLED' ? 'bg-danger-lt' : 'bg-warning-lt') }}">
                                    {{ $order->status }}
                                </span>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-6">
                                    <div class="text-secondary small text-uppercase fw-semibold">{{ __('ui.amount') }}</div>
                                    <div class="fw-semibold text-dark"><x-money :amount="(float) $order->total_amount" /></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-secondary small text-uppercase fw-semibold">{{ __('ui.date') }}</div>
                                    <div class="fw-semibold text-dark">{{ $order->created_at?->format('M d, Y') }}</div>
                                    <div class="small text-secondary">{{ $order->created_at?->format('H:i') }}</div>
                                </div>
                            </div>

                            <a href="{{ route('orders.show', $order) }}" class="btn btn-primary w-100 mt-3">{{ __('ui.view') }}</a>
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center text-secondary py-5">{{ __('ui.no_orders_yet') }}</div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</x-user-dashboard-layout>
