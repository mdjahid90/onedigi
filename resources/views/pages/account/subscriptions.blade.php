<x-user-dashboard-layout title="Subscriptions" pretitle="Account">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Subscriptions</h3>
        </div>

        <div class="card-body">
            <div class="row row-cards">
                @forelse($subscriptions as $item)
                    @php
                        $isActive = $item->subscription_expires_at && $item->subscription_expires_at->isFuture();
                    @endphp
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $item->title }}</div>
                                        <div class="text-secondary small">Order #{{ $item->order_id }}</div>
                                    </div>
                                    <span class="badge {{ $isActive ? 'bg-success-lt' : 'bg-secondary-lt' }}">
                                        {{ $isActive ? 'Active' : 'Expired' }}
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <div class="d-flex justify-content-between border-bottom py-2">
                                        <span class="text-secondary">Starts</span>
                                        <span class="fw-medium">{{ $item->subscription_starts_at?->format('M d, Y H:i') ?? 'Not set' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between border-bottom py-2">
                                        <span class="text-secondary">Expires</span>
                                        <span class="fw-medium">{{ $item->subscription_expires_at?->format('M d, Y H:i') ?? 'Not set' }}</span>
                                    </div>
                                </div>

                                @if($item->entitlement_notes)
                                    <div class="alert alert-info mt-3 mb-0">{{ $item->entitlement_notes }}</div>
                                @endif
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('orders.show', $item->order) }}" class="btn btn-outline-primary w-100">View Order</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty">
                            <p class="empty-title">No subscriptions yet</p>
                            <p class="empty-subtitle text-secondary">Delivered subscription products will appear here when admin adds the subscription dates.</p>
                            <div class="empty-action">
                                <a href="{{ route('products.index') }}" class="btn btn-primary">Browse Products</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        @if($subscriptions->hasPages())
            <div class="card-footer">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</x-user-dashboard-layout>
