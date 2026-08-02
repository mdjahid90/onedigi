<x-user-dashboard-layout :title="'Order #' . $order->id" pretitle="Order Details">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm font-semibold text-gray-900">{{ __('ui.order_summary') }}</div>
                <div class="mt-1 text-xs text-gray-600">{{ __('ui.order') }} #{{ $order->id }}</div>
            </div>

            @auth
                <div class="flex items-center gap-4">
                    <a href="{{ route('downloads.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Download Center</a>
                    <a href="{{ route('orders') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __('ui.my_orders') }}</a>
                </div>
            @else
                <a href="{{ route('home') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __('ui.home') }}</a>
            @endauth
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white border border-gray-100 rounded-lg p-4">
                    <div class="text-sm font-semibold text-gray-900">{{ __('ui.status') }}</div>
                    <div class="mt-2 inline-flex items-center rounded-md bg-gray-50 border border-gray-100 px-3 py-1 text-sm font-medium text-gray-900">{{ $order->status }}</div>

                    <div class="mt-4 text-xs text-gray-600 bg-gray-50 border border-gray-100 rounded-lg p-3">
                        {{ __('ui.delivery_note') }}
                    </div>

                    @if(in_array(strtoupper((string) $order->status), ['PENDING', 'CANCELLED'], true))
                        @php($activeGateways = $activeGateways ?? collect())
                        @if($activeGateways->count() === 1)
                            <form method="POST" action="{{ route('orders.retry_payment', $order) }}" class="mt-4">
                                @csrf
                                <input type="hidden" name="payment_gateway" value="{{ $activeGateways->first()->code }}">
                                <button class="inline-flex h-10 items-center rounded-md bg-indigo-600 px-4 text-sm font-medium text-white hover:bg-indigo-700">
                                    Retry Payment
                                </button>
                            </form>
                        @elseif($activeGateways->count() > 1)
                            <button type="button" id="open-retry-payment-methods" class="mt-4 inline-flex h-10 items-center rounded-md bg-indigo-600 px-4 text-sm font-medium text-white hover:bg-indigo-700">
                                Retry Payment
                            </button>
                        @else
                            <div class="mt-4 text-xs text-red-700 bg-red-50 border border-red-100 rounded-lg p-3">
                                No active payment gateway is configured.
                            </div>
                        @endif
                    @endif
                </div>

                @if(strtoupper((string) $order->status) === 'DELIVERED' && $order->delivery)
                    <div class="bg-white border border-gray-100 rounded-lg p-4">
                        <div class="text-sm font-semibold text-gray-900">{{ __('ui.delivery') }}</div>

                        @if($order->delivery->delivery_link)
                            <div class="mt-3 text-sm">
                                <div class="text-xs font-semibold text-gray-900">{{ __('ui.delivery_link') }}</div>
                                <a href="{{ $order->delivery->delivery_link }}" target="_blank" rel="noreferrer" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 break-all">{{ $order->delivery->delivery_link }}</a>
                            </div>
                        @endif

                        @if($order->delivery->file_path)
                            <div class="mt-3">
                                <a href="{{ route('orders.delivery.download', $order) }}" class="inline-flex h-10 items-center px-4 rounded-md bg-indigo-600 text-white text-sm font-medium">{{ __('ui.download_file') }}</a>
                            </div>
                        @endif

                        @if($order->delivery->notes)
                            <div class="mt-3">
                                <div class="text-xs font-semibold text-gray-900">{{ __('ui.notes') }}</div>
                                <div class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $order->delivery->notes }}</div>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="bg-white border border-gray-100 rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 text-sm font-semibold text-gray-900">{{ __('ui.items') }}</div>
                    <div class="divide-y divide-gray-100">
                        @foreach($order->items as $item)
                            <div class="px-4 py-3 flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $item->title }}</div>
                                    <div class="text-xs text-gray-600">{{ __('ui.qty') }}: {{ $item->quantity }}</div>
                                </div>
                                <div class="text-sm font-semibold text-gray-900"><x-money :amount="(float) $item->subtotal" /></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 text-sm font-semibold text-gray-900">{{ __('ui.transactions') }}</div>
                    <div class="divide-y divide-gray-100">
                        @forelse($order->transactions as $trx)
                            <div class="px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-semibold text-gray-900">{{ $trx->gateway }}</div>
                                    <div class="text-sm font-semibold text-gray-900"><x-money :amount="(float) $trx->amount" /></div>
                                </div>
                                <div class="mt-1 text-xs text-gray-600">TRX: {{ $trx->trx_id }} | Status: {{ $trx->status }}</div>
                            </div>
                        @empty
                            <div class="px-4 py-6 text-sm text-gray-600">{{ __('ui.no_transactions_logged') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-lg p-4 h-fit">
                <div class="text-sm font-semibold text-gray-900">{{ __('ui.customer') }}</div>
                <div class="mt-3 text-sm text-gray-700">
                    <div class="font-semibold text-gray-900">{{ $order->customer_name }}</div>
                    <div class="text-gray-600">{{ $order->customer_email }}</div>
                    <div class="text-gray-600">{{ $order->country }}</div>
                </div>

                @if($order->notes)
                    <div class="mt-4">
                        <div class="text-xs font-semibold text-gray-900">{{ __('ui.notes') }}</div>
                        <div class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $order->notes }}</div>
                    </div>
                @endif

                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
                    <span class="text-gray-600">{{ __('ui.total') }}</span>
                    <span class="font-semibold text-gray-900"><x-money :amount="(float) $order->total_amount" /></span>
                </div>
            </div>
        </div>
    </div>

    @if(in_array(strtoupper((string) $order->status), ['PENDING', 'CANCELLED'], true) && ($activeGateways ?? collect())->count() > 1)
        <div id="retry-payment-method-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
                <div class="flex items-start justify-between border-b border-gray-100 p-4">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Choose payment method</div>
                        <div class="mt-1 text-xs text-gray-500">Select one gateway to retry this payment.</div>
                    </div>
                    <button type="button" id="close-retry-payment-methods" class="rounded-md px-2 py-1 text-gray-500 hover:bg-gray-100">x</button>
                </div>

                <form method="POST" action="{{ route('orders.retry_payment', $order) }}">
                    @csrf
                    <div class="space-y-3 p-4">
                        @foreach($activeGateways as $gateway)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-3 hover:border-indigo-300">
                                <input type="radio" name="payment_gateway" value="{{ $gateway->code }}" class="text-indigo-600" required>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900">{{ $gateway->name }}</span>
                                    <span class="block text-xs text-gray-500">{{ strtoupper((string) $gateway->mode) }} mode</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="flex justify-end gap-2 border-t border-gray-100 p-4">
                        <button type="button" id="cancel-retry-payment-methods" class="h-10 rounded-md border border-gray-200 px-4 text-sm font-medium text-gray-700">Cancel</button>
                        <button class="h-10 rounded-md bg-indigo-600 px-4 text-sm font-medium text-white">Continue</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('retry-payment-method-modal');
                const openButton = document.getElementById('open-retry-payment-methods');
                const closeButtons = [
                    document.getElementById('close-retry-payment-methods'),
                    document.getElementById('cancel-retry-payment-methods'),
                ];

                const openModal = () => {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                };

                const closeModal = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                };

                openButton?.addEventListener('click', openModal);
                closeButtons.forEach((button) => button?.addEventListener('click', closeModal));
                modal?.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal();
                    }
                });
            });
        </script>
    @endif
</x-user-dashboard-layout>
