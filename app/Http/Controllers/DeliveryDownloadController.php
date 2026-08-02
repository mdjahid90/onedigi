<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliveryDownloadController extends Controller
{
    public function __invoke(Request $request, Order $order): StreamedResponse
    {
        $user = $request->user();

        $allowed = false;
        if ($request->hasValidSignature()) {
            $allowed = true;
        }

        if ($user && $order->user_id && (int) $order->user_id === (int) $user->id) {
            $allowed = true;
        }

        if (!$allowed) {
            $lastOrderId = $request->session()->get('last_order_id');
            if ($lastOrderId && (int) $lastOrderId === (int) $order->id) {
                $allowed = true;
            }
        }

        abort_unless($allowed, 403);

        $order->load('delivery');

        abort_unless($order->delivery && $order->delivery->file_path, 404);

        $disk = Storage::disk('deliveries')->exists($order->delivery->file_path)
            ? 'deliveries'
            : 'public';

        abort_unless(Storage::disk($disk)->exists($order->delivery->file_path), 404);

        return Storage::disk($disk)->download($order->delivery->file_path);
    }
}
