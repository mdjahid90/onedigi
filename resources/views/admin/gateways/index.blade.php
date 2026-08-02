<x-admin-layout>
    <x-slot name="header">
        <div>
            <div class="page-pretitle">Gateways</div>
            <h2 class="page-title">Payment Gateways</h2>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Supported gateways</h3>
                        <div class="card-subtitle">Only gateways already integrated in code can be configured here.</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter card-table text-nowrap">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Mode</th>
                                <th>Configuration</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gateways as $gateway)
                                @php
                                    $meta = $supported[$gateway->code] ?? [];
                                    $configured = \App\Services\PaymentGatewayService::isConfigured($gateway);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $meta['name'] ?? $gateway->name }}</div>
                                        <div class="text-secondary small">{{ $meta['description'] ?? '' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt">{{ strtoupper((string) $gateway->mode) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $configured ? 'bg-green-lt' : 'bg-warning-lt' }}">
                                            {{ $configured ? 'Configured' : 'Needs setup' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $gateway->is_active ? 'bg-green-lt' : 'bg-secondary-lt' }}">
                                            {{ $gateway->is_active ? 'On' : 'Off' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.gateways.edit', $gateway) }}" class="btn btn-primary btn-sm">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-6">
                                        No supported gateways are registered in code.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <div class="row g-2 justify-content-center justify-content-sm-between">
                        <div class="col-auto d-flex align-items-center">
                            <p class="m-0 text-secondary">
                                Showing {{ $gateways->firstItem() ?? 0 }} to {{ $gateways->lastItem() ?? 0 }} of {{ $gateways->total() }} entries
                            </p>
                        </div>
                        <div class="col-auto">{{ $gateways->onEachSide(1)->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
