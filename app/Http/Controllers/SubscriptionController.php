<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __invoke(Request $request): View
    {
        $subscriptions = OrderItem::query()
            ->whereNotNull('subscription_expires_at')
            ->whereHas('order', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status', 'DELIVERED');
            })
            ->with(['order', 'product'])
            ->latest('subscription_expires_at')
            ->paginate(12);

        return view('pages.account.subscriptions', [
            'subscriptions' => $subscriptions,
        ]);
    }
}
