<x-site-layout>
    @php($supportEmail = trim((string) \App\Models\Setting::getValue('support_email', '')))
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white/70 backdrop-blur border border-white/40 shadow-lg rounded-2xl p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Contact Us</div>
                        <div class="mt-1 text-xs text-gray-600">We usually reply within 24 hours.</div>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-indigo-600/10 text-indigo-700 flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8a2 2 0 01-2 2H5l-2 2V6a2 2 0 012-2h14a2 2 0 012 2v2z" />
                        </svg>
                    </div>
                </div>

                <form method="POST" action="{{ route('page.contact.submit') }}" class="mt-6 space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-200" />
                            @error('name')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input name="email" type="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-200" />
                            @error('email')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject</label>
                        <input name="subject" value="{{ old('subject') }}" class="mt-1 block w-full rounded-md border-gray-200" />
                        @error('subject')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea name="message" rows="6" class="mt-1 block w-full rounded-md border-gray-200">{{ old('message') }}</textarea>
                        @error('message')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <button class="w-full h-11 rounded-md bg-indigo-600 text-white text-sm font-medium">Send Message</button>
                </form>
            </div>

            <div class="space-y-6">
                <div class="bg-white/70 backdrop-blur border border-white/40 shadow-lg rounded-2xl p-6">
                    <div class="text-sm font-semibold text-gray-900">Contact Information</div>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-xl bg-gray-900/5 text-gray-700 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-gray-900">Email</div>
                                @if($supportEmail !== '')
                                    <a href="mailto:{{ $supportEmail }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ $supportEmail }}</a>
                                @else
                                    <div class="text-sm text-gray-500">—</div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-xl bg-gray-900/5 text-gray-700 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12.414a2 2 0 00-2.828 0L6.343 16.657M12 12V6" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-gray-900">Address</div>
                                <div class="text-sm text-gray-700">Dhaka, Bangladesh</div>
                                <div class="text-xs text-gray-500">(Update this section with your business address)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur border border-white/40 shadow-lg rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/40">
                        <div class="text-sm font-semibold text-gray-900">Map</div>
                        <div class="mt-1 text-xs text-gray-600">Find us easily.</div>
                    </div>
                    <div class="aspect-[16/9] bg-gray-100">
                        <iframe class="w-full h-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=Dhaka%2C%20Bangladesh&output=embed"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
