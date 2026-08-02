<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="page-pretitle">Payment gateway</div>
                <h2 class="page-title">{{ $meta['name'] ?? $gateway->name }}</h2>
            </div>
            <a href="{{ route('admin.gateways.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="{{ route('admin.gateways.update', $gateway) }}">
                @csrf
                @method('PUT')

                <div class="row row-cards">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title">API configuration</h3>
                                    <div class="card-subtitle">Update credentials for the integrated gateway. New gateways require code integration first.</div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Gateway</label>
                                    <input value="{{ $meta['name'] ?? $gateway->name }}" class="form-control" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Base URL</label>
                                    <input name="base_url" value="{{ old('base_url', $gateway->base_url) }}" class="form-control" placeholder="https://pay.your-domain.com">
                                    @if($gateway->code === \App\Services\PaymentGatewayService::PIPRAPAY)
                                        <div class="form-hint">Use your PipraPay base URL. The system will call <span class="font-monospace">/api/create-charge</span> and verify with <span class="font-monospace">/api/verify-payment</span>.</div>
                                    @else
                                        <div class="form-hint">Use your UddoktaPay base URL. The system will call <span class="font-monospace">/api/checkout-v2</span> and <span class="font-monospace">/api/verify-payment</span>.</div>
                                    @endif
                                    @error('base_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">API Key</label>
                                    <textarea name="api_key" rows="3" class="form-control font-monospace small">{{ old('api_key', $gateway->api_key) }}</textarea>
                                    @error('api_key')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Secret Key</label>
                                    <textarea name="secret_key" rows="2" class="form-control font-monospace small">{{ old('secret_key', $gateway->secret_key) }}</textarea>
                                    <div class="form-hint">Optional. Keep blank if this gateway does not require a separate secret.</div>
                                    @error('secret_key')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Mode</label>
                                        <select name="mode" class="form-select">
                                            <option value="TEST" {{ old('mode', $gateway->mode) === 'TEST' ? 'selected' : '' }}>Test</option>
                                            <option value="LIVE" {{ old('mode', $gateway->mode) === 'LIVE' ? 'selected' : '' }}>Live</option>
                                        </select>
                                        @error('mode')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6 d-flex align-items-end">
                                        <label class="form-check form-switch mb-2">
                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $gateway->is_active) ? 'checked' : '' }}>
                                            <span class="form-check-label">Enable this payment gateway</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-end">
                                <button class="btn btn-primary">Save Gateway</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Status</h3>
                            </div>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex align-items-center justify-content-between">
                                    <span>Configuration</span>
                                    <span class="badge {{ $isConfigured ? 'bg-green-lt' : 'bg-warning-lt' }}">{{ $isConfigured ? 'Ready' : 'Missing' }}</span>
                                </div>
                                <div class="list-group-item d-flex align-items-center justify-content-between">
                                    <span>Checkout status</span>
                                    <span class="badge {{ $gateway->is_active ? 'bg-green-lt' : 'bg-secondary-lt' }}">{{ $gateway->is_active ? 'On' : 'Off' }}</span>
                                </div>
                                <div class="list-group-item">
                                    <div class="text-secondary small">When one gateway is on, checkout goes directly through it. When multiple supported gateways are on, customers will choose one before payment.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
