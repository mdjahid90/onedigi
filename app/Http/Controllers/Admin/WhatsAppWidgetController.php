<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppWidgetController extends Controller
{
    public function edit(): View
    {
        return view('admin.whatsapp-widget.edit', [
            'enabled' => (bool) ((int) Setting::getValue('whatsapp_widget_enabled', '1')),
            'number' => Setting::getValue('whatsapp_number', ''),
            'message' => Setting::getValue('whatsapp_widget_message', ''),
            'color' => Setting::getValue('whatsapp_widget_color', '#25D366'),
            'right' => (int) Setting::getValue('whatsapp_widget_right', '20'),
            'bottomMobile' => (int) Setting::getValue('whatsapp_widget_bottom_mobile', '96'),
            'bottomDesktop' => (int) Setting::getValue('whatsapp_widget_bottom_desktop', '24'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable'],
            'number' => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'max:20', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'right' => ['required', 'integer', 'min:0', 'max:400'],
            'bottom_mobile' => ['required', 'integer', 'min:0', 'max:600'],
            'bottom_desktop' => ['required', 'integer', 'min:0', 'max:600'],
        ]);

        $enabled = $request->boolean('enabled');
        $numberRaw = (string) ($validated['number'] ?? '');
        $number = preg_replace('/\D+/', '', $numberRaw);

        Setting::setValue('whatsapp_widget_enabled', $enabled ? '1' : '0');
        Setting::setValue('whatsapp_number', $number);
        Setting::setValue('whatsapp_widget_message', (string) ($validated['message'] ?? ''));
        Setting::setValue('whatsapp_widget_color', (string) ($validated['color'] ?? '#25D366'));
        Setting::setValue('whatsapp_widget_right', (string) $validated['right']);
        Setting::setValue('whatsapp_widget_bottom_mobile', (string) $validated['bottom_mobile']);
        Setting::setValue('whatsapp_widget_bottom_desktop', (string) $validated['bottom_desktop']);

        return back()->with('success', 'WhatsApp widget updated successfully.');
    }
}
