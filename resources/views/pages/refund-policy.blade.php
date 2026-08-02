<x-site-layout>
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white/70 backdrop-blur border border-white/40 shadow-lg rounded-2xl p-6">
            <div class="text-sm font-semibold text-gray-900">Refund Policy</div>

            <div class="mt-6 prose prose-sm max-w-none">
                @if(!empty($policy))
                    {!! nl2br(e($policy)) !!}
                @else
                    <p>Digital products are non-refundable once delivered.</p>
                    <p>If the file is corrupted or not working as described, user must report within 24 hours.</p>
                    <p>Admin will review the claim within 72 hours.</p>
                    <p>If approved, user may receive file replacement or partial refund depending on case.</p>
                    <p>Illegal usage, re-selling or redistribution automatically voids refund rights.</p>
                @endif
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('home') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">← Back to Home</a>
            </div>
        </div>
    </div>
</x-site-layout>
