<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="h3 fw-semibold text-dark">Footer Editor</h2>
            <div class="mt-1 small text-secondary">Manage footer content shown on the public website.</div>
        </div>
    </x-slot>

    @php
        $socialsInitial = $footerSocials ?? [];
        $socialsOldNames = old('footer_socials_name');
        $socialsOldUrls = old('footer_socials_url');
        $socialsOldEnabled = old('footer_socials_enabled');

        if (is_array($socialsOldNames)) {
            $socialsInitial = [];

            foreach ($socialsOldNames as $i => $name) {
                $socialsInitial[] = [
                    'name' => $name,
                    'url' => is_array($socialsOldUrls) ? ($socialsOldUrls[$i] ?? '') : '',
                    'enabled' => (bool) ((int) (is_array($socialsOldEnabled) ? ($socialsOldEnabled[$i] ?? 1) : 1)),
                ];
            }
        }
    @endphp

    <div class="space-y-6">
        <form method="POST" action="{{ route('admin.footer.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="card card-body">
                <div class="small fw-semibold text-dark">Branding</div>
                <div class="mt-1 small text-secondary">Logo and title displayed in the footer.</div>

                <div class="mt-5">
                    @if(!empty($footerLogo))
                        <div class="mb-3">
                            <img src="{{ Storage::url($footerLogo) }}" alt="Footer Logo" class="h-12 w-auto border border-gray-200 rounded-xl" />
                        </div>
                    @endif
                    <input type="file" name="footer_logo" accept="image/*" class="form-control w-full" />
                    @error('footer_logo')<div class="mt-1 small text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mt-5">
                    <label class="block small fw-medium text-body">Footer Title</label>
                    <input name="footer_title" value="{{ old('footer_title', $footerTitle) }}" class="form-control w-full mt-1" />
                    @error('footer_title')<div class="mt-1 small text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card card-body">
                <div class="small fw-semibold text-dark">Description</div>
                <div class="mt-1 small text-secondary">Short text displayed next to the footer logo.</div>

                <div class="mt-5">
                    <textarea name="footer_description" rows="4" class="form-control w-full">{{ old('footer_description', $footerDescription) }}</textarea>
                    @error('footer_description')<div class="mt-1 small text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card card-body" x-data="{ socials: @js($socialsInitial) }">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="small fw-semibold text-dark">Social Icons</div>
                        <div class="mt-1 small text-secondary">Add social profiles (name + URL) and enable/disable each icon.</div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" @click="socials.push({ name: '', url: '', enabled: true })">Add Social</button>
                </div>

                <div class="mt-5 space-y-3">
                    <template x-for="(social, index) in socials" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                            <div class="md:col-span-5">
                                <label class="block small fw-medium text-body">Name</label>
                                <input class="form-control w-full mt-1" type="text" name="footer_socials_name[]" x-model="social.name" placeholder="Facebook" />
                            </div>
                            <div class="md:col-span-6">
                                <label class="block small fw-medium text-body">URL</label>
                                <input class="form-control w-full mt-1" type="text" name="footer_socials_url[]" x-model="social.url" placeholder="https://" />
                                <input type="hidden" name="footer_socials_enabled[]" :value="social.enabled ? 1 : 0" />
                            </div>
                            <div class="md:col-span-1 flex md:justify-end pt-7">
                                <div class="flex items-center gap-1">
                                    <label class="btn btn-outline-secondary btn-sm cursor-pointer" aria-label="Toggle">
                                        <input type="checkbox" class="form-check-input form-check-input-sm" x-model="social.enabled" />
                                    </label>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="socials.splice(index, 1)" aria-label="Remove">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="socials.length === 0" class="small text-secondary">No social links added yet.</div>
                </div>
            </div>

            <div class="card card-body">
                <div class="small fw-semibold text-dark">Copyright</div>
                <div class="mt-1 small text-secondary">Displayed at the bottom of the footer.</div>

                <div class="mt-5">
                    <input name="footer_copyright" value="{{ old('footer_copyright', $footerCopyright) }}" class="form-control w-full" />
                    @error('footer_copyright')<div class="mt-1 small text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">Save Footer</button>
            </div>
        </form>
    </div>
</x-admin-layout>
