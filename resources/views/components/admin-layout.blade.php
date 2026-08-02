@php
    $headerHtml = isset($header) ? trim((string) $header) : '';
    $slotHtml = trim((string) $slot);
    $hasTablerHeader = $headerHtml !== '' && str_contains($headerHtml, 'page-header');
    $hasTablerBody = str_contains($slotHtml, 'page-body');
@endphp

<x-app-layout>
    <div class="root-print">
        @if ($headerHtml !== '')
            @if ($hasTablerHeader)
                {!! $headerHtml !!}
            @else
                <div class="page-header d-print-none">
                    <div class="container-xl">
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                {!! $headerHtml !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @if ($hasTablerBody)
            {!! $slotHtml !!}
        @else
            <div class="page-body">
                <div class="container-xl">
                    <div class="admin-page-stack">
                        {!! $slotHtml !!}
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
