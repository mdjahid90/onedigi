<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function edit(): View
    {
        $seo = SeoSetting::query()->first() ?? new SeoSetting();

        return view('admin.marketing.seo', [
            'seo' => $seo,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'global_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:5000'],
            'meta_keywords' => ['nullable', 'string', 'max:5000'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $seo = SeoSetting::query()->first() ?? SeoSetting::create([]);

        $update = [
            'global_title' => $validated['global_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'author_name' => $validated['author_name'] ?? null,
        ];

        $uploadFailed = false;

        try {
            if ($request->hasFile('og_image')) {
                $update['og_image'] = $request->file('og_image')->store('seo', 'public');
            }
        } catch (\Throwable $e) {
            $uploadFailed = true;
            Log::error('SEO og_image upload failed', ['message' => $e->getMessage()]);
        }

        $seo->update($update);

        if ($uploadFailed) {
            return back()->with('warning', 'SEO settings saved, but OG image upload failed.');
        }

        return back()->with('success', 'SEO settings updated successfully.');
    }
}
