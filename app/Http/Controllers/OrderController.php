<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('pages.orders', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $user = $request->user();

        $allowed = false;

        if ($user && $order->user_id && $order->user_id === $user->id) {
            $allowed = true;
        }

        if (!$allowed) {
            $lastOrderId = $request->session()->get('last_order_id');
            if ($lastOrderId && (int) $lastOrderId === (int) $order->id) {
                $allowed = true;
            }
        }

        abort_unless($allowed, 403);

        $order->load(['items', 'transactions', 'delivery']);

        return view('pages.order-show', [
            'order' => $order,
            'activeGateways' => PaymentGatewayService::activeGateways(),
        ]);
    }
}
