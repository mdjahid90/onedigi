<x-admin-layout>
    <x-slot name="header">
        <div>
            <div class="page-pretitle">Brand settings</div>
            <h2 class="page-title">Brand Settings</h2>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="{{ route('admin.branding.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="row row-cards">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title">Logo & favicon</h3>
                                    <div class="card-subtitle">Update the brand assets used across the admin panel and public website.</div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Header Logo</label>
                                        <div class="border rounded bg-white p-3 mb-3 d-flex align-items-center justify-content-center" style="min-height: 86px;">
                                            @if(!empty($settings['logo_light']))
                                                <img src="{{ Storage::url($settings['logo_light']) }}" alt="Header Logo" class="img-fluid" style="max-height: 48px;">
                                            @else
                                                <span class="text-secondary small">No header logo uploaded</span>
                                            @endif
                                        </div>
                                        <input type="file" name="logo_light" accept="image/*" class="form-control">
                                        <div class="form-hint">Recommended transparent PNG or SVG-style image. Max 2 MB.</div>
                                        @error('logo_light')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Footer Logo</label>
                                        <div class="border rounded bg-white p-3 mb-3 d-flex align-items-center justify-content-center" style="min-height: 86px;">
                                            @if(!empty($settings['logo_footer']))
                                                <img src="{{ Storage::url($settings['logo_footer']) }}" alt="Footer Logo" class="img-fluid" style="max-height: 48px;">
                                            @else
                                                <span class="text-secondary small">No footer logo uploaded</span>
                                            @endif
                                        </div>
                                        <input type="file" name="logo_footer" accept="image/*" class="form-control">
                                        <div class="form-hint">Used in footer sections where a separate logo is needed. Max 2 MB.</div>
                                        @error('logo_footer')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Favicon</label>
                                        <div class="border rounded bg-white p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 86px; height: 86px;">
                                            @if(!empty($settings['favicon']))
                                                <img src="{{ Storage::url($settings['favicon']) }}" alt="Favicon" class="img-fluid rounded" style="max-width: 42px; max-height: 42px;">
                                            @else
                                                <span class="text-secondary small text-center">No icon</span>
                                            @endif
                                        </div>
                                        <input type="file" name="favicon" accept="image/*" class="form-control">
                                        <div class="form-hint">Square PNG/ICO works best. Max 1 MB.</div>
                                        @error('favicon')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Current status</h3>
                            </div>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex align-items-center justify-content-between">
                                    <span>Header logo</span>
                                    <span class="badge {{ !empty($settings['logo_light']) ? 'bg-green-lt' : 'bg-secondary-lt' }}">
                                        {{ !empty($settings['logo_light']) ? 'Uploaded' : 'Missing' }}
                                    </span>
                                </div>
                                <div class="list-group-item d-flex align-items-center justify-content-between">
                                    <span>Footer logo</span>
                                    <span class="badge {{ !empty($settings['logo_footer']) ? 'bg-green-lt' : 'bg-secondary-lt' }}">
                                        {{ !empty($settings['logo_footer']) ? 'Uploaded' : 'Missing' }}
                                    </span>
                                </div>
                                <div class="list-group-item d-flex align-items-center justify-content-between">
                                    <span>Favicon</span>
                                    <span class="badge {{ !empty($settings['favicon']) ? 'bg-green-lt' : 'bg-secondary-lt' }}">
                                        {{ !empty($settings['favicon']) ? 'Uploaded' : 'Missing' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
