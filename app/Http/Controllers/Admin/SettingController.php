<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'siteTitle' => Setting::getValue('site_title', config('app.name')),
            'supportEmail' => Setting::getValue('support_email', ''),
            'logoLight' => Setting::getValue('logo_light', ''),
            'logoDark' => Setting::getValue('logo_dark', ''),
            'favicon' => Setting::getValue('favicon', ''),
            'reviewsEnabled' => Setting::getValue('reviews_enabled', '1') === '1',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_title' => ['required', 'string', 'max:255'],
            'support_email' => ['required', 'email', 'max:255'],
            'logo_light' => ['nullable', 'file', 'max:5120'],
            'logo_dark' => ['nullable', 'file', 'max:5120'],
            'favicon' => ['nullable', 'file', 'max:2048'],
            'reviews_enabled' => ['nullable', 'boolean'],
        ]);

        Setting::setValue('site_title', $validated['site_title']);
        Setting::setValue('support_email', $validated['support_email']);
        Setting::setValue('reviews_enabled', $request->boolean('reviews_enabled') ? '1' : '0');

        $uploadFailed = false;

        try {
            if ($request->hasFile('logo_light')) {
                $path = $request->file('logo_light')->store('settings', 'public');
                Setting::setValue('logo_light', $path);
            }

            if ($request->hasFile('logo_dark')) {
                $path = $request->file('logo_dark')->store('settings', 'public');
                Setting::setValue('logo_dark', $path);
            }

            if ($request->hasFile('favicon')) {
                $path = $request->file('favicon')->store('settings', 'public');
                Setting::setValue('favicon', $path);
            }
        } catch (\Throwable $e) {
            $uploadFailed = true;
            Log::error('Settings upload failed', [
                'message' => $e->getMessage(),
            ]);
        }

        if ($uploadFailed) {
            return back()->with('warning', 'Settings saved, but one or more files could not be uploaded.');
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
