<x-admin-layout>
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">Orders</div>
                        <h2 class="page-title">Update Order #{{ $order->id }}</h2>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row row-cards">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Order Status</h3>
                            </div>
                            <div class="card-body">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>
                                            {{ ucfirst(strtolower($status)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="card-footer text-end">
                                <button class="btn btn-primary">Save Status</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Delivery</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-secondary">
                                    Upload a file, paste a delivery link, or fill subscription/license details below. Delivering marks the order as delivered.
                                </p>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Delivery Link</label>
                                        <input name="delivery_link" value="{{ old('delivery_link', $order->delivery?->delivery_link) }}" class="form-control">
                                        @error('delivery_link')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Delivery File</label>
                                        <input type="file" name="delivery_file" class="form-control">
                                        @if(!empty($order->delivery?->file_path))
                                            <div class="form-hint">Existing file: {{ $order->delivery->file_path }}</div>
                                        @endif
                                        @error('delivery_file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Delivery Notes</label>
                                        <textarea name="delivery_notes" rows="3" class="form-control">{{ old('delivery_notes', $order->delivery?->notes) }}</textarea>
                                        @error('delivery_notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title">Subscriptions & Licenses</h3>
                                    <div class="text-secondary small">These fields power the customer Subscriptions and Licenses pages.</div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row row-cards">
                                    @foreach($order->items as $item)
                                        <div class="col-12">
                                            <div class="border rounded p-3">
                                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                                    <div>
                                                        <div class="fw-semibold">{{ $item->title }}</div>
                                                        <div class="text-secondary small">Qty: {{ $item->quantity }} · Order item #{{ $item->id }}</div>
                                                    </div>
                                                    <div class="text-end fw-semibold">&#2547;{{ number_format((float) $item->subtotal, 2) }}</div>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Subscription Starts</label>
                                                        <input type="datetime-local" name="items[{{ $item->id }}][subscription_starts_at]" value="{{ old('items.'.$item->id.'.subscription_starts_at', $item->subscription_starts_at?->format('Y-m-d\TH:i')) }}" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Subscription Expires</label>
                                                        <input type="datetime-local" name="items[{{ $item->id }}][subscription_expires_at]" value="{{ old('items.'.$item->id.'.subscription_expires_at', $item->subscription_expires_at?->format('Y-m-d\TH:i')) }}" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Access Email</label>
                                                        <input name="items[{{ $item->id }}][access_email]" value="{{ old('items.'.$item->id.'.access_email', $item->access_email) }}" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Access Password</label>
                                                        <input name="items[{{ $item->id }}][access_password]" value="{{ old('items.'.$item->id.'.access_password', $item->access_password) }}" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">License Key</label>
                                                        <textarea name="items[{{ $item->id }}][license_key]" rows="4" class="form-control">{{ old('items.'.$item->id.'.license_key', $item->license_key) }}</textarea>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Customer Notes</label>
                                                        <textarea name="items[{{ $item->id }}][entitlement_notes]" rows="4" class="form-control">{{ old('items.'.$item->id.'.entitlement_notes', $item->entitlement_notes) }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button name="deliver" value="1" class="btn btn-success">Save Delivery & Entitlements</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
