<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $baseQuery = Order::query()->where('user_id', $user->id);

        $totalOrders = (clone $baseQuery)->count();
        $deliveredOrders = (clone $baseQuery)->where('status', 'DELIVERED')->count();
        $activeOrders = (clone $baseQuery)->whereIn('status', ['PENDING', 'PROCESSING'])->count();
        $totalSpent = (float) (clone $baseQuery)
            ->where('status', '!=', 'CANCELLED')
            ->sum('total_amount');

        $downloadableOrders = (clone $baseQuery)
            ->where('status', 'DELIVERED')
            ->whereHas('delivery', function ($query) {
                $query->whereNotNull('file_path')
                    ->orWhereNotNull('delivery_link');
            })
            ->count();

        $recentOrders = (clone $baseQuery)
            ->with('delivery')
            ->latest()
            ->limit(8)
            ->get();

        $latestDownloadOrder = (clone $baseQuery)
            ->where('status', 'DELIVERED')
            ->whereHas('delivery', function ($query) {
                $query->whereNotNull('file_path')
                    ->orWhereNotNull('delivery_link');
            })
            ->with('delivery')
            ->latest()
            ->first();

        return view('dashboard', [
            'totalOrders' => $totalOrders,
            'deliveredOrders' => $deliveredOrders,
            'activeOrders' => $activeOrders,
            'downloadableOrders' => $downloadableOrders,
            'totalSpent' => $totalSpent,
            'recentOrders' => $recentOrders,
            'latestDownloadOrder' => $latestDownloadOrder,
        ]);
    }
}
