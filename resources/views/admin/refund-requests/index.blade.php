<x-admin-layout>
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">Commerce</div>
                        <h2 class="page-title">Refund Requests</h2>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Refund Requests</h3>
                    <div class="ms-auto btn-list">
                        <a href="{{ route('admin.refund_requests.index') }}" class="btn btn-sm {{ $currentStatus === '' ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                        @foreach($statuses as $status)
                            <a href="{{ route('admin.refund_requests.index', ['status' => $status]) }}" class="btn btn-sm {{ $currentStatus === $status ? 'btn-primary' : 'btn-outline-secondary' }}">
                                {{ ucfirst(strtolower($status)) }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Customer</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Admin Note</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($refundRequests as $requestItem)
                                <tr>
                                    <td style="min-width: 240px;">
                                        <div class="fw-semibold">#{{ $requestItem->id }} · {{ $requestItem->reason }}</div>
                                        <div class="text-secondary small">{{ $requestItem->message ?: 'No customer message.' }}</div>
                                        <div class="text-secondary small">{{ $requestItem->created_at?->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $requestItem->user?->name ?? 'Customer' }}</div>
                                        <div class="text-secondary small">{{ $requestItem->user?->email }}</div>
                                    </td>
                                    <td>
                                        @if($requestItem->order)
                                            <a href="{{ route('admin.orders.show', $requestItem->order) }}" class="fw-semibold">#{{ $requestItem->order_id }}</a>
                                            <div class="text-secondary small">&#2547;{{ number_format((float) $requestItem->order->total_amount, 2) }}</div>
                                        @else
                                            <span class="text-secondary">Deleted order</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $requestItem->status === 'APPROVED' ? 'bg-success-lt' : ($requestItem->status === 'REJECTED' ? 'bg-danger-lt' : 'bg-warning-lt') }}">
                                            {{ $requestItem->status }}
                                        </span>
                                    </td>
                                    <td style="min-width: 260px;">
                                        <form method="POST" action="{{ route('admin.refund_requests.update', $requestItem) }}" id="refund-request-{{ $requestItem->id }}">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-select mb-2">
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status }}" @selected($requestItem->status === $status)>{{ ucfirst(strtolower($status)) }}</option>
                                                @endforeach
                                            </select>
                                            <textarea name="admin_note" rows="2" class="form-control" placeholder="Visible to customer">{{ $requestItem->admin_note }}</textarea>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <button form="refund-request-{{ $requestItem->id }}" class="btn btn-primary btn-sm">Save</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-5">No refund requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <div class="row g-2 justify-content-center justify-content-sm-between">
                        <div class="col-auto d-flex align-items-center">
                            <p class="m-0 text-secondary">
                                Showing {{ $refundRequests->firstItem() ?? 0 }} to {{ $refundRequests->lastItem() ?? 0 }} of {{ $refundRequests->total() }} entries
                            </p>
                        </div>
                        <div class="col-auto">
                            {{ $refundRequests->onEachSide(1)->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
