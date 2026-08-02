<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RefundRequest;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefundRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = RefundRequest::query()
            ->where('user_id', $request->user()->id)
            ->with('order')
            ->latest()
            ->paginate(10);

        $eligibleOrders = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('status', '!=', 'CANCELLED')
            ->latest()
            ->limit(50)
            ->get();

        return view('pages.account.refunds', [
            'refundRequests' => $requests,
            'eligibleOrders' => $eligibleOrders,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:3000'],
        ]);

        $order = Order::query()
            ->where('id', $validated['order_id'])
            ->where('user_id', $request->user()->id)
            ->where('status', '!=', 'CANCELLED')
            ->firstOrFail();

        $openRequestExists = RefundRequest::query()
            ->where('order_id', $order->id)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', [RefundRequest::STATUS_PENDING, RefundRequest::STATUS_REVIEWING])
            ->exists();

        if ($openRequestExists) {
            return back()->withErrors([
                'order_id' => 'This order already has an open refund request.',
            ])->withInput();
        }

        $refundRequest = RefundRequest::query()->create([
            'user_id' => $request->user()->id,
            'order_id' => $order->id,
            'status' => RefundRequest::STATUS_PENDING,
            'reason' => $validated['reason'],
            'message' => $validated['message'] ?? null,
        ]);

        AdminNotificationService::create(
            'refund_request_created',
            'Refund request #'.$refundRequest->id,
            $request->user()->name.' requested a refund for order #'.$order->id.'.',
            route('admin.refund_requests.index'),
            'warning',
            $refundRequest
        );

        return back()->with('success', 'Refund request submitted.');
    }
}
