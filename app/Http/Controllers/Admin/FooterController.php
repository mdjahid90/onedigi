<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FooterController extends Controller
{
    public function edit(): View
    {
        $defaultLinks = [
            ['label' => 'Privacy Policy', 'url' => route('page.privacy')],
            ['label' => 'Terms & Conditions', 'url' => route('page.terms')],
            ['label' => 'AML Policy', 'url' => route('page.aml')],
            ['label' => 'Refund Policy', 'url' => route('page.refund-policy')],
            ['label' => 'FAQ', 'url' => route('page.faq')],
            ['label' => 'API', 'url' => route('page.api')],
            ['label' => 'Contact Us', 'url' => route('page.contact')],
        ];

        $linksRaw = Setting::query()->where('key', 'footer_links')->value('value');
        if ($linksRaw === null) {
            Setting::setValue('footer_links', json_encode($defaultLinks));
            $linksRaw = json_encode($defaultLinks);
        }

        $links = json_decode((string) $linksRaw, true);
        $socials = json_decode((string) Setting::getValue('footer_socials', '[]'), true);

        $links = is_array($links) ? $links : [];
        $socials = is_array($socials) ? $socials : [];

        $socials = array_values(array_map(static function ($social) {
            $name = is_array($social) ? ($social['name'] ?? '') : '';
            $url = is_array($social) ? ($social['url'] ?? '') : '';
            $enabled = is_array($social) ? ($social['enabled'] ?? true) : true;

            return [
                'name' => (string) $name,
                'url' => (string) $url,
                'enabled' => (bool) $enabled,
            ];
        }, $socials));

        return view('admin.footer.edit', [
            'footerLogo' => Setting::getValue('footer_logo', ''),
            'footerTitle' => Setting::getValue('footer_title', ''),
            'footerDescription' => Setting::getValue('footer_description', ''),
            'footerLinks' => $links,
            'footerSocials' => $socials,
            'footerCopyright' => Setting::getValue('footer_copyright', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'footer_logo' => ['nullable', 'file', 'image', 'max:2048'],
            'footer_title' => ['nullable', 'string', 'max:120'],
            'footer_description' => ['nullable', 'string', 'max:2000'],
            'footer_links_label' => ['nullable', 'array'],
            'footer_links_label.*' => ['nullable', 'string', 'max:120'],
            'footer_links_url' => ['nullable', 'array'],
            'footer_links_url.*' => ['nullable', 'string', 'max:255'],
            'footer_socials_name' => ['nullable', 'array'],
            'footer_socials_name.*' => ['nullable', 'string', 'max:60'],
            'footer_socials_url' => ['nullable', 'array'],
            'footer_socials_url.*' => ['nullable', 'string', 'max:255'],
            'footer_socials_enabled' => ['nullable', 'array'],
            'footer_socials_enabled.*' => ['nullable'],
            'footer_copyright' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::setValue('footer_title', $validated['footer_title'] ?? '');
        Setting::setValue('footer_description', $validated['footer_description'] ?? '');
        Setting::setValue('footer_copyright', $validated['footer_copyright'] ?? '');

        if ($request->has('footer_links_label') || $request->has('footer_links_url')) {
            $links = [];
            $labels = $validated['footer_links_label'] ?? [];
            $urls = $validated['footer_links_url'] ?? [];
            $count = max(count($labels), count($urls));
            for ($i = 0; $i < $count; $i++) {
                $label = trim((string) ($labels[$i] ?? ''));
                $url = trim((string) ($urls[$i] ?? ''));
                if ($label !== '' && $url !== '') {
                    $links[] = ['label' => $label, 'url' => $url];
                }
            }
            Setting::setValue('footer_links', json_encode($links));
        }

        $socials = [];
        $names = $validated['footer_socials_name'] ?? [];
        $socialUrls = $validated['footer_socials_url'] ?? [];
        $enabledValues = $validated['footer_socials_enabled'] ?? [];
        $socialCount = max(count($names), count($socialUrls));
        for ($i = 0; $i < $socialCount; $i++) {
            $name = trim((string) ($names[$i] ?? ''));
            $url = trim((string) ($socialUrls[$i] ?? ''));
            $enabled = (bool) ((int) ($enabledValues[$i] ?? 1));
            if ($name !== '' && $url !== '') {
                $socials[] = ['name' => $name, 'url' => $url, 'enabled' => $enabled];
            }
        }
        Setting::setValue('footer_socials', json_encode($socials));

        if ($request->hasFile('footer_logo')) {
            $old = Setting::getValue('footer_logo', '');
            if (!empty($old) && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            $path = $request->file('footer_logo')->store('footer', 'public');
            Setting::setValue('footer_logo', $path);
        }

        return back()->with('success', 'Footer updated successfully.');
    }
}
