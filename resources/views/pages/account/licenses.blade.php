<x-user-dashboard-layout title="Licenses" pretitle="Account">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Licenses</h3>
        </div>

        <div class="card-body">
            <div class="row row-cards">
                @forelse($licenses as $item)
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $item->title }}</div>
                                        <div class="text-secondary small">Order #{{ $item->order_id }}</div>
                                    </div>
                                    <a href="{{ route('orders.show', $item->order) }}" class="btn btn-outline-primary btn-sm">Order</a>
                                </div>

                                <div class="row g-3 mt-2">
                                    @if($item->access_email)
                                        <div class="col-md-6">
                                            <label class="form-label">Access Email</label>
                                            <input class="form-control" value="{{ $item->access_email }}" readonly>
                                        </div>
                                    @endif
                                    @if($item->access_password)
                                        <div class="col-md-6">
                                            <label class="form-label">Access Password</label>
                                            <input class="form-control" value="{{ $item->access_password }}" readonly>
                                        </div>
                                    @endif
                                    @if($item->license_key)
                                        <div class="col-12">
                                            <label class="form-label">License Key</label>
                                            <textarea class="form-control" rows="4" readonly>{{ $item->license_key }}</textarea>
                                        </div>
                                    @endif
                                    @if($item->entitlement_notes)
                                        <div class="col-12">
                                            <div class="alert alert-info mb-0">{{ $item->entitlement_notes }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty">
                            <p class="empty-title">No licenses available</p>
                            <p class="empty-subtitle text-secondary">License keys or account credentials from delivered orders will be listed here.</p>
                            <div class="empty-action">
                                <a href="{{ route('downloads.index') }}" class="btn btn-primary">Open Downloads</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        @if($licenses->hasPages())
            <div class="card-footer">
                {{ $licenses->links() }}
            </div>
        @endif
    </div>
</x-user-dashboard-layout>
