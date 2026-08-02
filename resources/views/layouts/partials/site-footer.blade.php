<footer class="relative overflow-hidden border-t border-slate-200 bg-white">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-24 left-1/4 h-64 w-64 rounded-full bg-emerald-200/30 blur-3xl"></div>
        <div class="absolute -bottom-24 right-1/4 h-64 w-64 rounded-full bg-sky-200/30 blur-3xl"></div>
    </div>

    @php
        $autoTranslate = app(\App\Services\AutoTranslate::class);
        $locale = app()->getLocale();
        $logoLight = \App\Models\Setting::getValue('logo_light', '');
        $logoFooter = \App\Models\Setting::getValue('logo_footer', '');
        $footerLogo = \App\Models\Setting::getValue('footer_logo', '');
        $footerTitle = \App\Models\Setting::getValue('footer_title', '');
        $footerDescription = \App\Models\Setting::getValue('footer_description', '');
        $footerLinks = json_decode((string) \App\Models\Setting::getValue('footer_links', '[]'), true);
        $footerSocials = json_decode((string) \App\Models\Setting::getValue('footer_socials', '[]'), true);
        $footerCopyright = trim((string) \App\Models\Setting::getValue('footer_copyright', ''));
        $supportEmail = trim((string) \App\Models\Setting::getValue('support_email', ''));

        $footerLinks = is_array($footerLinks) ? $footerLinks : [];
        $footerSocials = is_array($footerSocials) ? $footerSocials : [];

        $footerLinks = array_values(array_filter($footerLinks, static function ($link) {
            $label = is_array($link) ? trim((string) ($link['label'] ?? '')) : '';
            $url = is_array($link) ? trim((string) ($link['url'] ?? '')) : '';

            return $label !== '' && $url !== '';
        }));

        $defaultFooterLabels = [
            'Privacy Policy',
            'Terms & Conditions',
            'Refund Policy',
            'FAQ',
            'Contact Us',
            'AML Policy',
            'API',
        ];

        $resolvedFooterLinks = array_map(function ($link) use ($defaultFooterLabels, $locale, $autoTranslate) {
            $linkLabel = is_array($link) ? ($link['label'] ?? '') : '';
            $linkUrl = is_array($link) ? ($link['url'] ?? '') : '';
            $isDefaultLabel = in_array((string) $linkLabel, $defaultFooterLabels, true);
            $labelToShow = $isDefaultLabel ? __('ui.footer_' . \Illuminate\Support\Str::slug((string) $linkLabel, '_')) : (string) $linkLabel;
            $labelToShow = (!$isDefaultLabel && $locale !== 'en')
                ? $autoTranslate->translate((string) $labelToShow, $locale, false)
                : (string) $labelToShow;

            return [
                'label' => $labelToShow,
                'url' => (string) $linkUrl,
            ];
        }, $footerLinks);

        $linkChunkSize = max(1, (int) ceil(max(1, count($resolvedFooterLinks)) / 2));
        $footerLinkColumns = array_chunk($resolvedFooterLinks, $linkChunkSize);

        $footerSocials = array_values(array_filter($footerSocials, static function ($social) {
            $name = is_array($social) ? trim((string) ($social['name'] ?? '')) : '';
            $url = is_array($social) ? trim((string) ($social['url'] ?? '')) : '';
            $enabled = is_array($social) ? (bool) ($social['enabled'] ?? true) : true;

            return $enabled && $name !== '' && $url !== '';
        }));

        $allowedSocials = ['facebook', 'youtube', 'instagram', 'telegram', 'whatsapp'];
        $footerSocials = array_values(array_filter($footerSocials, static function ($social) use ($allowedSocials) {
            $name = is_array($social) ? strtolower(trim((string) ($social['name'] ?? ''))) : '';
            $slug = (string) \Illuminate\Support\Str::of($name)->replace([' ', '-', '_'], '');

            return in_array($slug, $allowedSocials, true);
        }));

        $footerTitleTrimmed = trim((string) $footerTitle);
        $hasLongFooterTitle = $footerTitleTrimmed !== '' && mb_strlen($footerTitleTrimmed) > 32;
        $brandTitle = $hasLongFooterTitle
            ? (string) config('app.name')
            : ($footerTitleTrimmed !== '' ? $footerTitleTrimmed : (string) config('app.name'));
        $footerCopyrightFallbackName = $footerTitleTrimmed !== '' ? $footerTitleTrimmed : (string) config('app.name', 'Digify');
        $addressLine = $hasLongFooterTitle ? $footerTitleTrimmed : '';
    @endphp

    <div class="relative mx-auto max-w-7xl px-4 pb-6 pt-8 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <div class="lg:col-span-6">
                <div class="flex items-center gap-2.5">
                    @if(!empty($footerLogo))
                        <img src="{{ Storage::url($footerLogo) }}" alt="Logo" class="block h-9 w-auto" width="162" height="36" loading="lazy" decoding="async" />
                    @elseif(!empty($logoFooter))
                        <img src="{{ Storage::url($logoFooter) }}" alt="Logo" class="block h-9 w-auto" width="162" height="36" loading="lazy" decoding="async" />
                    @elseif(!empty($logoLight))
                        <img src="{{ Storage::url($logoLight) }}" alt="Logo" class="block h-9 w-auto" width="162" height="36" loading="lazy" decoding="async" />
                    @else
                        <x-application-logo class="block h-9 w-auto fill-current text-slate-900" />
                    @endif
                </div>

                @if($addressLine !== '')
                    <div class="mt-2 text-sm font-medium leading-6 text-slate-700">{{ $addressLine }}</div>
                @endif

                @if(!empty($footerDescription))
                    <div class="mt-3 max-w-xl text-sm leading-6 text-slate-600">{{ $footerDescription }}</div>
                @else
                    <div class="mt-3 max-w-xl text-sm leading-6 text-slate-600">Premium digital subscriptions with reliable delivery and seamless account access.</div>
                @endif

                @if($supportEmail !== '')
                    <div class="mt-4">
                        <a href="mailto:{{ $supportEmail }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9 6 9-6m-18 0v10a2 2 0 002 2h14a2 2 0 002-2V7" /></svg>
                            {{ $supportEmail }}
                        </a>
                    </div>
                @endif

                @if(count($footerSocials) > 0)
                    <div class="mt-4">
                        <nav class="social-menu" aria-label="Social links">
                            <ul>
                        @foreach($footerSocials as $social)
                            @php($socialName = is_array($social) ? ($social['name'] ?? '') : '')
                            @php($socialUrl = is_array($social) ? ($social['url'] ?? '') : '')
                            @php($socialKey = strtolower(trim((string) $socialName)))
                            @php($socialSlug = (string) \Illuminate\Support\Str::of($socialKey)->replace([' ', '-', '_'], ''))
                            @php($socialIcon = 'fa-solid fa-link')
                            @if($socialSlug === 'facebook')
                                @php($socialInitial = 'f')
                            @elseif($socialSlug === 'youtube')
                                @php($socialInitial = '▶')
                            @elseif($socialSlug === 'instagram')
                                @php($socialInitial = '◎')
                            @elseif($socialSlug === 'telegram')
                                @php($socialInitial = '↗')
                            @elseif($socialSlug === 'whatsapp')
                                @php($socialInitial = '☏')
                            @endif
                            <li class="social-{{ $socialSlug }}">
                                <a href="{{ $socialUrl }}" target="_blank" rel="noreferrer" aria-label="{{ $socialName }}">
                                    <i class="{{ match ($socialSlug) {
                                        'facebook' => 'fab fa-facebook-f',
                                        'youtube' => 'fab fa-youtube',
                                        'instagram' => 'fab fa-instagram',
                                        'telegram' => 'fab fa-telegram-plane',
                                        'whatsapp' => 'fab fa-whatsapp',
                                        default => 'fa-solid fa-link',
                                    } }}" aria-hidden="true"></i>
                                </a>
                                <span class="tooltip">{{ $socialName }}</span>
                            </li>
                        @endforeach
                            </ul>
                        </nav>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-6">
                <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Navigation</div>
                @if(count($resolvedFooterLinks) > 0)
                    <div class="mt-3 grid grid-cols-2 gap-x-8 gap-y-2">
                        @foreach($footerLinkColumns as $column)
                            <div class="space-y-2">
                                @foreach($column as $link)
                                    <a href="{{ $link['url'] }}" class="block text-sm text-slate-600 transition hover:text-slate-900">{{ $link['label'] }}</a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 text-sm text-slate-500">Footer links will appear here once configured.</div>
                @endif
            </div>
        </div>

        <div class="mt-7 border-t border-slate-200 pt-4">
            <div class="flex flex-col gap-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    @if($footerCopyright !== '')
                        {{ $footerCopyright }}
                    @else
                        &copy; {{ date('Y') }} {{ $footerCopyrightFallbackName }}. All rights reserved.
                    @endif
                </div>
                <div class="text-slate-400">Crafted for reliable digital commerce.</div>
            </div>
        </div>
    </div>
</footer>
