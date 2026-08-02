<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\AdminNotificationService;
use App\Services\AnalyticsService;
use App\Services\EmailTemplateRenderer;
use App\Services\UserNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()->latest()->paginate(15);

        return view('admin.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'transactions', 'delivery']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function edit(Order $order): View
    {
        $order->load(['delivery', 'items.product']);

        return view('admin.orders.edit', [
            'order' => $order,
            'statuses' => ['PENDING', 'PROCESSING', 'DELIVERED', 'CANCELLED'],
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        if ($request->has('deliver')) {
            $validated = $request->validate([
                'delivery_link' => ['nullable', 'url', 'max:2048'],
                'delivery_file' => ['nullable', 'file', 'max:51200'],
                'delivery_notes' => ['nullable', 'string', 'max:5000'],
                'items' => ['nullable', 'array'],
                'items.*.subscription_starts_at' => ['nullable', 'date'],
                'items.*.subscription_expires_at' => ['nullable', 'date'],
                'items.*.license_key' => ['nullable', 'string', 'max:5000'],
                'items.*.access_email' => ['nullable', 'string', 'max:255'],
                'items.*.access_password' => ['nullable', 'string', 'max:255'],
                'items.*.entitlement_notes' => ['nullable', 'string', 'max:5000'],
            ]);

            $existingDelivery = Delivery::query()->where('order_id', $order->id)->first();

            $hasLink = !empty($validated['delivery_link'] ?? null);
            $hasFile = $request->hasFile('delivery_file');
            $hasExisting = !empty($existingDelivery?->delivery_link) || !empty($existingDelivery?->file_path);
            $hasEntitlement = collect($validated['items'] ?? [])->contains(function (array $row) {
                return !empty($row['subscription_expires_at'])
                    || !empty($row['license_key'])
                    || !empty($row['access_email'])
                    || !empty($row['access_password']);
            });

            abort_unless($hasLink || $hasFile || $hasExisting || $hasEntitlement, 422);

            $filePath = null;
            if ($hasFile) {
                $filePath = $request->file('delivery_file')->store('', 'deliveries');
            }

            Delivery::query()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'delivery_link' => $hasLink ? $validated['delivery_link'] : ($existingDelivery?->delivery_link),
                    'file_path' => $filePath ?? ($existingDelivery?->file_path),
                    'notes' => array_key_exists('delivery_notes', $validated) ? $validated['delivery_notes'] : ($existingDelivery?->notes),
                    'delivered_at' => Carbon::now(),
                    'delivered_by' => $request->user()?->id,
                ]
            );

            $order->update([
                'status' => 'DELIVERED',
            ]);

            AnalyticsService::record('order_delivered', [
                'user_id' => $request->user()?->id,
                'subject_type' => 'order',
                'subject_id' => $order->id,
                'route_name' => $request->route()?->getName(),
                'path' => '/'.$request->path(),
                'ip_address' => $request->ip(),
                'session_hash' => $request->hasSession() ? hash('sha256', (string) $request->session()->getId()) : null,
            ]);

            AdminNotificationService::create(
                'order_delivered',
                'Order #'.$order->id.' delivered',
                $request->user()?->name.' saved delivery details.',
                route('admin.orders.show', $order),
                'success',
                $order
            );

            UserNotificationService::create(
                $order->user_id,
                'order_delivered',
                'Order #'.$order->id.' delivered',
                'Your order has been delivered. Delivery details are ready in your dashboard.',
                route('orders.show', $order),
                'success',
                $order
            );

            foreach (($validated['items'] ?? []) as $itemId => $row) {
                $item = $order->items()->whereKey($itemId)->first();

                if (!$item) {
                    continue;
                }

                $item->update([
                    'subscription_starts_at' => $row['subscription_starts_at'] ?? null,
                    'subscription_expires_at' => $row['subscription_expires_at'] ?? null,
                    'license_key' => $row['license_key'] ?? null,
                    'access_email' => $row['access_email'] ?? null,
                    'access_password' => $row['access_password'] ?? null,
                    'entitlement_notes' => $row['entitlement_notes'] ?? null,
                ]);
            }

            $renderer = new EmailTemplateRenderer();

            $deliveryDownloadUrl = URL::temporarySignedRoute(
                'orders.delivery.download',
                now()->addDays(30),
                $order
            );

            $rendered = $renderer->render('Order Delivered', [
                'name' => $order->customer_name,
                'order_id' => $order->id,
                'amount' => number_format((float) $order->total_amount, 0),
                'delivery_link' => (string) ($validated['delivery_link'] ?? ''),
                'delivery_download_url' => $deliveryDownloadUrl,
            ]);

            try {
                Mail::send([], [], function ($message) use ($order, $rendered) {
                    $message->to($order->customer_email)
                        ->subject($rendered['subject'])
                        ->html($rendered['body']);
                });
            } catch (\Throwable $e) {
                Log::error('Order delivered email send failed', [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]);
            }

            return back()->with('success', 'Delivery details saved.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:PENDING,PROCESSING,DELIVERED,CANCELLED'],
        ]);

        $previousStatus = (string) $order->status;

        $order->update([
            'status' => $validated['status'],
        ]);

        if ($previousStatus !== $validated['status']) {
            $eventType = match ($validated['status']) {
                'CANCELLED' => 'order_cancelled',
                'DELIVERED' => 'order_delivered',
                'PROCESSING' => 'order_processing',
                default => 'order_status_changed',
            };

            AnalyticsService::record($eventType, [
                'user_id' => $request->user()?->id,
                'subject_type' => 'order',
                'subject_id' => $order->id,
                'route_name' => $request->route()?->getName(),
                'path' => '/'.$request->path(),
                'ip_address' => $request->ip(),
                'session_hash' => $request->hasSession() ? hash('sha256', (string) $request->session()->getId()) : null,
                'meta' => [
                    'from' => $previousStatus,
                    'to' => $validated['status'],
                ],
            ]);

            AdminNotificationService::create(
                $eventType,
                'Order #'.$order->id.' status changed',
                $previousStatus.' -> '.$validated['status'],
                route('admin.orders.show', $order),
                $validated['status'] === 'CANCELLED' ? 'warning' : 'info',
                $order
            );

            $userTitle = match ($validated['status']) {
                'PROCESSING' => 'Order #'.$order->id.' is processing',
                'DELIVERED' => 'Order #'.$order->id.' delivered',
                'CANCELLED' => 'Order #'.$order->id.' cancelled',
                default => 'Order #'.$order->id.' status updated',
            };

            $userBody = match ($validated['status']) {
                'PROCESSING' => 'Your payment is confirmed and your order is now being processed.',
                'DELIVERED' => 'Your order has been marked as delivered. Open the order to view delivery details.',
                'CANCELLED' => 'Your order has been cancelled. Contact support if you need help.',
                default => 'Order status changed from '.$previousStatus.' to '.$validated['status'].'.',
            };

            UserNotificationService::create(
                $order->user_id,
                $eventType,
                $userTitle,
                $userBody,
                route('orders.show', $order),
                $validated['status'] === 'CANCELLED' ? 'warning' : ($validated['status'] === 'DELIVERED' ? 'success' : 'info'),
                $order
            );
        }

        return back()->with('success', 'Order status updated.');
    }
}
