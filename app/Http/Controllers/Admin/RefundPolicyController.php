<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefundPolicyController extends Controller
{
    public function edit(): View
    {
        $policy = Setting::getValue('refund_policy', '');

        return view('admin.refund-policy.edit', compact('policy'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'policy' => ['required', 'string', 'max:20000'],
        ]);

        Setting::setValue('refund_policy', $validated['policy']);

        return back()->with('success', 'Refund policy updated successfully.');
    }
}
