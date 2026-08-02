<x-user-dashboard-layout title="Downloads" pretitle="Download Center">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Available Downloads</h2>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($downloads as $order)
                    <div class="px-5 py-4">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Order #{{ $order->id }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Delivered on {{ $order->created_at?->format('Y-m-d H:i') }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if($order->delivery?->file_path)
                                    <a href="{{ route('orders.delivery.download', $order) }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black transition">
                                        Download File
                                    </a>
                                @endif

                                @if($order->delivery?->delivery_link)
                                    <a href="{{ $order->delivery->delivery_link }}" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                                        Open Link
                                    </a>
                                @endif

                                <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center rounded-lg border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 transition">
                                    Order Details
                                </a>
                            </div>
                        </div>

                        @if($order->delivery?->notes)
                            <div class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                Note: {{ $order->delivery->notes }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm font-semibold text-slate-700">No downloadable files yet.</p>
                        <p class="mt-1 text-xs text-slate-500">After your order is delivered, download button will appear here.</p>
                        <a href="{{ route('products.index') }}" class="mt-4 inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black transition">
                            Browse Products
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-4">
            {{ $downloads->links() }}
        </div>
    </div>
</x-user-dashboard-layout>
