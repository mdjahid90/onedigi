<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function edit(): View
    {
        return view('admin.currency.edit', [
            'usdRateBdt' => (string) Setting::getValue('currency_usd_rate_bdt', ''),
            'rubRateBdt' => (string) Setting::getValue('currency_rub_rate_bdt', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'usd_rate_bdt' => ['required', 'numeric', 'min:0.000001'],
            'rub_rate_bdt' => ['required', 'numeric', 'min:0.000001'],
        ]);

        Setting::setValue('currency_usd_rate_bdt', (string) $validated['usd_rate_bdt']);
        Setting::setValue('currency_rub_rate_bdt', (string) $validated['rub_rate_bdt']);

        return back()->with('success', 'Currency rates updated successfully.');
    }
}
