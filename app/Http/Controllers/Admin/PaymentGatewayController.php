<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\PaymentGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    public function index(): View
    {
        PaymentGatewayService::syncSupportedGateways();

        $gateways = PaymentGateway::query()
            ->whereIn('code', array_keys(PaymentGatewayService::supported()))
            ->orderBy('sort_order')
            ->paginate(15);

        return view('admin.gateways.index', [
            'gateways' => $gateways,
            'supported' => PaymentGatewayService::supported(),
        ]);
    }

    public function create(): View
    {
        abort(404);
    }

    public function store(Request $request): RedirectResponse
    {
        abort(404);
    }

    public function edit(PaymentGateway $gateway): View
    {
        abort_unless(array_key_exists((string) $gateway->code, PaymentGatewayService::supported()), 404);

        return view('admin.gateways.edit', [
            'gateway' => $gateway,
            'meta' => PaymentGatewayService::supported()[$gateway->code],
            'isConfigured' => PaymentGatewayService::isConfigured($gateway),
        ]);
    }

    public function update(Request $request, PaymentGateway $gateway): RedirectResponse
    {
        $validated = $request->validate([
            'base_url' => ['nullable', 'url', 'max:2048'],
            'api_key' => ['nullable', 'string', 'max:5000'],
            'secret_key' => ['nullable', 'string', 'max:5000'],
            'mode' => ['required', 'in:TEST,LIVE'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        abort_unless(array_key_exists((string) $gateway->code, PaymentGatewayService::supported()), 404);

        $gateway->update([
            'base_url' => $validated['base_url'] ?? null,
            'api_key' => $validated['api_key'] ?? null,
            'secret_key' => $validated['secret_key'] ?? null,
            'mode' => $validated['mode'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return back()->with('success', 'Payment gateway updated successfully.');
    }

    public function destroy(PaymentGateway $gateway): RedirectResponse
    {
        abort(404);
    }
}
