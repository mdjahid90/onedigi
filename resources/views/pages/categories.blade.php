<x-site-layout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between">
            <div class="text-sm font-semibold text-gray-900">{{ __('ui.categories') }}</div>
            <a href="{{ route('products.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __('ui.browse_products') }}</a>
        </div>

        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @forelse($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="bg-white border border-gray-100 rounded-lg p-4 hover:border-gray-200">
                    <div class="flex items-center gap-3">
                        @if(!empty($category->icon))
                            <img src="{{ Storage::url($category->icon) }}" alt="{{ $category->name }}" class="h-8 w-8 rounded-md object-cover border border-gray-100" />
                        @else
                            <div class="h-8 w-8 rounded-md bg-gray-100 border border-gray-100"></div>
                        @endif
                        <div class="text-sm font-semibold text-gray-900">{{ $category->name }}</div>
                    </div>
                </a>
            @empty
                <div class="text-sm text-gray-500">{{ __('ui.no_categories_yet') }}</div>
            @endforelse
        </div>
    </div>
</x-site-layout>
