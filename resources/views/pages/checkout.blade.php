<x-site-layout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between">
            <div class="text-sm font-semibold text-gray-900">Checkout</div>
            <a href="{{ route('cart') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Back to cart</a>
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white border border-gray-100 rounded-lg p-4">
                <form method="POST" action="{{ route('checkout.store') }}" class="space-y-4" id="checkout-form">
                    @csrf
                    @php($activeGateways = $activeGateways ?? collect())

                    <div class="text-xs text-gray-600 bg-gray-50 border border-gray-100 rounded-lg p-3">
                        Delivery after payment via Email &amp; Dashboard within 10min - 12 hours.
                    </div>

                    @guest
                        <div class="text-xs text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-lg p-3">
                            Enter a password. After Place Order, your account will be created automatically, you will be logged in, and the order flow will continue.
                        </div>
                    @endguest

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input name="full_name" value="{{ old('full_name', auth()->user()->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-200" />
                        @error('full_name')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input name="email" type="email" value="{{ old('email', auth()->user()->email ?? '') }}" class="mt-1 block w-full rounded-md border-gray-200" />
                        @error('email')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Country</label>
                        <input name="country" value="{{ old('country') }}" class="mt-1 block w-full rounded-md border-gray-200" />
                        @error('country')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                    </div>

                    @guest
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password</label>
                                <input name="password" type="password" required class="mt-1 block w-full rounded-md border-gray-200" />
                                @error('password')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input name="password_confirmation" type="password" required class="mt-1 block w-full rounded-md border-gray-200" />
                            </div>
                        </div>
                    @endguest

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                        <textarea name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-200">{{ old('notes') }}</textarea>
                        @error('notes')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                    </div>

                    @if($activeGateways->count() === 1)
                        <input type="hidden" name="payment_gateway" value="{{ $activeGateways->first()->code }}">
                        <div class="text-xs text-gray-600 bg-gray-50 border border-gray-100 rounded-lg p-3">
                            Payment method: <span class="font-semibold text-gray-900">{{ $activeGateways->first()->name }}</span>
                        </div>
                        <button class="w-full h-11 rounded-md bg-indigo-600 text-white text-sm font-medium">Place Order</button>
                    @elseif($activeGateways->count() > 1)
                        <input type="hidden" name="payment_gateway" id="selected-payment-gateway" value="{{ old('payment_gateway') }}">
                        @error('payment_gateway')<div class="text-xs text-red-600">{{ $message }}</div>@enderror
                        <button type="button" id="open-payment-methods" class="w-full h-11 rounded-md bg-indigo-600 text-white text-sm font-medium">Choose Payment Method</button>
                    @else
                        @error('payment_gateway')<div class="text-xs text-red-600">{{ $message }}</div>@enderror
                        <div class="text-xs text-red-700 bg-red-50 border border-red-100 rounded-lg p-3">
                            No active payment gateway is configured. Please contact support.
                        </div>
                        <button type="button" disabled class="w-full h-11 rounded-md bg-gray-300 text-white text-sm font-medium cursor-not-allowed">Payment Unavailable</button>
                    @endif
                </form>
            </div>

            <div class="bg-white border border-gray-100 rounded-lg p-4 h-fit">
                <div class="text-sm font-semibold text-gray-900">Order Summary</div>

                <div class="mt-4 space-y-3">
                    @foreach($items as $item)
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $item['product']->title }}</div>
                                <div class="text-xs text-gray-600">Qty: {{ $item['quantity'] }}</div>
                            </div>
                            <div class="text-sm font-semibold text-gray-900"><x-money :amount="(float) $item['subtotal']" /></div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
                    <span class="text-gray-600">Total</span>
                    <span class="font-semibold text-gray-900"><x-money :amount="(float) $total" /></span>
                </div>
            </div>
        </div>
    </div>

    @if(($activeGateways ?? collect())->count() > 1)
        <div id="payment-method-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
                <div class="flex items-start justify-between border-b border-gray-100 p-4">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Choose payment method</div>
                        <div class="mt-1 text-xs text-gray-500">Select one gateway to continue payment.</div>
                    </div>
                    <button type="button" id="close-payment-methods" class="rounded-md px-2 py-1 text-gray-500 hover:bg-gray-100">x</button>
                </div>

                <div class="space-y-3 p-4">
                    @foreach($activeGateways as $gateway)
                        <label class="payment-method-option flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-3 hover:border-indigo-300">
                            <input type="radio" name="payment_gateway_choice" value="{{ $gateway->code }}" class="text-indigo-600" {{ old('payment_gateway') === $gateway->code ? 'checked' : '' }}>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900">{{ $gateway->name }}</span>
                                <span class="block text-xs text-gray-500">{{ strtoupper((string) $gateway->mode) }} mode</span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-100 p-4">
                    <button type="button" id="cancel-payment-methods" class="h-10 rounded-md border border-gray-200 px-4 text-sm font-medium text-gray-700">Cancel</button>
                    <button type="button" id="confirm-payment-method" class="h-10 rounded-md bg-indigo-600 px-4 text-sm font-medium text-white">Continue</button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('checkout-form');
                const selectedInput = document.getElementById('selected-payment-gateway');
                const modal = document.getElementById('payment-method-modal');
                const openButton = document.getElementById('open-payment-methods');
                const closeButtons = [
                    document.getElementById('close-payment-methods'),
                    document.getElementById('cancel-payment-methods'),
                ];
                const confirmButton = document.getElementById('confirm-payment-method');

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

                confirmButton?.addEventListener('click', function () {
                    const selected = modal.querySelector('input[name="payment_gateway_choice"]:checked');
                    if (!selected) {
                        return;
                    }

                    selectedInput.value = selected.value;
                    if (form.requestSubmit) {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                });
            });
        </script>
    @endif
</x-site-layout>
