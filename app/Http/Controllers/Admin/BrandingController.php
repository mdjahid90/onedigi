<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'logo_light' => Setting::getValue('logo_light', ''),
            'logo_footer' => Setting::getValue('logo_footer', ''),
            'favicon' => Setting::getValue('favicon', ''),
        ];

        return view('admin.branding.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'logo_light' => ['nullable', 'file', 'image', 'max:2048'],
            'logo_footer' => ['nullable', 'file', 'image', 'max:2048'],
            'favicon' => ['nullable', 'file', 'image', 'max:1024'],
        ]);

        foreach (['logo_light', 'logo_footer', 'favicon'] as $key) {
            if ($request->hasFile($key)) {
                $old = Setting::getValue($key);
                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file($key)->store('branding', 'public');
                Setting::setValue($key, $path);
            }
        }

        return back()->with('success', 'Branding settings updated successfully.');
    }
}
