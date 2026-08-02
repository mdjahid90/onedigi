<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\SiteVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function edit(): View
    {
        $verification = SiteVerification::query()->first() ?? new SiteVerification();

        return view('admin.marketing.verification', [
            'verification' => $verification,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'google_search_console' => ['nullable', 'string', 'max:10000'],
            'bing_webmaster' => ['nullable', 'string', 'max:10000'],
            'yandex' => ['nullable', 'string', 'max:10000'],
            'pinterest' => ['nullable', 'string', 'max:10000'],
        ]);

        $verification = SiteVerification::query()->first() ?? SiteVerification::create([]);

        $verification->update([
            'google_search_console' => $validated['google_search_console'] ?? null,
            'bing_webmaster' => $validated['bing_webmaster'] ?? null,
            'yandex' => $validated['yandex'] ?? null,
            'pinterest' => $validated['pinterest'] ?? null,
        ]);

        return back()->with('success', 'Site verification settings updated successfully.');
    }
}
