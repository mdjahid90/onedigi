<x-user-dashboard-layout title="Refunds" pretitle="Account">
    <div class="row row-cards">
        <div class="col-lg-5">
            <form method="POST" action="{{ route('refunds.store') }}" class="card">
                @csrf
                <div class="card-header">
                    <h3 class="card-title">Request Refund</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Order</label>
                        <select name="order_id" class="form-select" required>
                            <option value="">Select order</option>
                            @foreach($eligibleOrders as $order)
                                <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>
                                    #{{ $order->id }} - {{ $order->status }} - {{ number_format((float) $order->total_amount, 2) }} {{ $order->currency }}
                                </option>
                            @endforeach
                        </select>
                        @error('order_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <select name="reason" class="form-select" required>
                            @foreach(['Delivery issue', 'Wrong product', 'Duplicate payment', 'Product not working', 'Other'] as $reason)
                                <option value="{{ $reason }}" @selected(old('reason') === $reason)>{{ $reason }}</option>
                            @endforeach
                        </select>
                        @error('reason')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="form-label">Message</label>
                        <textarea name="message" rows="5" class="form-control" placeholder="Explain the issue clearly.">{{ old('message') }}</textarea>
                        @error('message')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary" @disabled($eligibleOrders->isEmpty())>Submit Request</button>
                </div>
            </form>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Refund Requests</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($refundRequests as $requestItem)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $requestItem->reason }}</div>
                                        @if($requestItem->admin_note)
                                            <div class="text-secondary small">Admin: {{ $requestItem->admin_note }}</div>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('orders.show', $requestItem->order) }}">#{{ $requestItem->order_id }}</a></td>
                                    <td>
                                        <span class="badge {{ in_array($requestItem->status, ['APPROVED'], true) ? 'bg-success-lt' : ($requestItem->status === 'REJECTED' ? 'bg-danger-lt' : 'bg-warning-lt') }}">
                                            {{ $requestItem->status }}
                                        </span>
                                    </td>
                                    <td class="text-secondary">{{ $requestItem->created_at?->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-5">No refund requests yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($refundRequests->hasPages())
                    <div class="card-footer">
                        {{ $refundRequests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-user-dashboard-layout>
