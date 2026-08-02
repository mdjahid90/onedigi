<x-site-layout>
    @php
        $title = $title ?? $page->title;
        $content = $content ?? $page->content;
    @endphp
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white/70 backdrop-blur border border-white/40 shadow-lg rounded-2xl p-6">
            <div class="text-sm font-semibold text-gray-900">{{ $title }}</div>

            <div class="mt-6 prose prose-sm max-w-none">
                {!! (string) ($content ?? '') !!}
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('home') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">← {{ __('ui.back_to_home') }}</a>
            </div>
        </div>
    </div>
</x-site-layout>
