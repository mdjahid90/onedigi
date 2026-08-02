@php
    $adminFooterCopyright = trim((string) \App\Models\Setting::getValue('footer_copyright', ''));
    $adminFooterFallbackName = trim((string) \App\Models\Setting::getValue('footer_title', ''));
    $adminFooterFallbackName = $adminFooterFallbackName !== '' ? $adminFooterFallbackName : config('app.name', 'Digify');
@endphp

<footer class="footer footer-transparent d-print-none admin-footer-shell">
    <div class="container-xl">
        <div class="row text-center align-items-center">
            <div class="col-12">
                <div class="text-secondary">
                    @if($adminFooterCopyright !== '')
                        {{ $adminFooterCopyright }}
                    @else
                        &copy; {{ now()->year }} {{ $adminFooterFallbackName }}. All rights reserved.
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>
