<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __invoke(): View
    {
        [$period, $start, $end, $isCustom] = $this->resolvePeriod();
        $previous = $this->previousWindow($start, $end);

        $eventsReady = Schema::hasTable('analytics_events');
        $events = $eventsReady
            ? AnalyticsEvent::query()
                ->whereBetween('occurred_at', [$start, $end])
                ->get(['event_type', 'occurred_at', 'path', 'session_hash', 'ip_address', 'country_code', 'country_name', 'device_type', 'browser', 'source'])
            : collect();

        $previousEvents = $eventsReady
            ? AnalyticsEvent::query()
                ->whereBetween('occurred_at', [$previous['start'], $previous['end']])
                ->get(['event_type', 'occurred_at', 'path', 'session_hash', 'ip_address'])
            : collect();

        $orders = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->get(['id', 'status', 'total_amount', 'currency', 'created_at']);

        $previousOrders = Order::query()
            ->whereBetween('created_at', [$previous['start'], $previous['end']])
            ->get(['id', 'status', 'total_amount', 'created_at']);

        $transactions = Transaction::query()
            ->whereBetween('created_at', [$start, $end])
            ->get(['status', 'amount', 'gateway', 'created_at']);

        $previousTransactions = Transaction::query()
            ->whereBetween('created_at', [$previous['start'], $previous['end']])
            ->get(['status', 'amount', 'created_at']);

        $pageViews = $events->where('event_type', 'page_view');
        $previousPageViews = $previousEvents->where('event_type', 'page_view');
        $visitors = $this->uniqueVisitors($pageViews);
        $previousVisitors = $this->uniqueVisitors($previousPageViews);

        $completedTransactions = $transactions->where('status', 'COMPLETED');
        $completedTransactionCount = $completedTransactions->count();
        $transactionCount = $transactions->count();
        $paymentRevenue = (float) $completedTransactions->sum('amount');
        $successRate = $transactionCount > 0 ? ($completedTransactionCount / $transactionCount) * 100 : 0.0;

        $previousCompletedTransactions = $previousTransactions->where('status', 'COMPLETED');
        $previousTransactionCount = $previousTransactions->count();
        $previousSuccessRate = $previousTransactionCount > 0
            ? ($previousCompletedTransactions->count() / $previousTransactionCount) * 100
            : 0.0;

        $orderCount = $orders->count();
        $deliveredOrders = $orders->where('status', 'DELIVERED');
        $orderRevenue = (float) $deliveredOrders->sum('total_amount');
        $averageOrderValue = $deliveredOrders->count() > 0 ? $orderRevenue / $deliveredOrders->count() : 0.0;
        $conversionRate = $visitors > 0 ? ($orderCount / $visitors) * 100 : 0.0;
        $previousConversionRate = $previousVisitors > 0 ? ($previousOrders->count() / $previousVisitors) * 100 : 0.0;

        $bucket = $this->bucketSeries($start, $end, $pageViews, $orders, $transactions);
        $topProducts = $this->topProducts($start, $end);

        return view('admin.analytics.index', [
            'period' => $period,
            'periodOptions' => $this->periodOptions(),
            'isCustom' => $isCustom,
            'start' => $start,
            'end' => $end,
            'previousStart' => $previous['start'],
            'previousEnd' => $previous['end'],
            'dateRange' => $start->format('M d, Y').' - '.$end->format('M d, Y'),
            'eventsReady' => $eventsReady,
            'metrics' => [
                'views' => $pageViews->count(),
                'previous_views' => $previousPageViews->count(),
                'visitors' => $visitors,
                'previous_visitors' => $previousVisitors,
                'orders' => $orderCount,
                'previous_orders' => $previousOrders->count(),
                'delivered_orders' => $deliveredOrders->count(),
                'revenue' => $orderRevenue,
                'payment_revenue' => $paymentRevenue,
                'average_order' => $averageOrderValue,
                'conversion_rate' => $conversionRate,
                'previous_conversion_rate' => $previousConversionRate,
                'transactions' => $transactionCount,
                'completed_transactions' => $completedTransactionCount,
                'success_rate' => $successRate,
                'previous_success_rate' => $previousSuccessRate,
            ],
            'series' => $bucket,
            'topPages' => $this->topDimension($pageViews, 'path', 'Unknown page'),
            'countries' => $this->topDimension($pageViews, 'country_name', 'Unknown', 8),
            'devices' => $this->topDimension($pageViews, 'device_type', 'Unknown', 5),
            'sources' => $this->topDimension($pageViews, 'source', 'Direct', 5),
            'browsers' => $this->topDimension($pageViews, 'browser', 'Other', 5),
            'eventTypes' => $this->topDimension($events, 'event_type', 'Unknown', 8),
            'gateways' => $this->topDimension($transactions, 'gateway', 'Unknown', 6),
            'topProducts' => $topProducts,
        ]);
    }

    private function resolvePeriod(): array
    {
        $period = (string) request()->query('period', 'this_month');
        $startInput = request()->query('start');
        $endInput = request()->query('end');

        $today = Carbon::today();
        $now = Carbon::now();

        if ($period === 'custom' && $startInput && $endInput) {
            $start = Carbon::parse((string) $startInput)->startOfDay();
            $end = Carbon::parse((string) $endInput)->endOfDay();

            if ($start->gt($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            return [$period, $start, $end, true];
        }

        return match ($period) {
            'today' => [$period, $today->copy()->startOfDay(), $today->copy()->endOfDay(), false],
            'yesterday' => [$period, $today->copy()->subDay()->startOfDay(), $today->copy()->subDay()->endOfDay(), false],
            'last_7_days' => [$period, $today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay(), false],
            'last_30_days' => [$period, $today->copy()->subDays(29)->startOfDay(), $today->copy()->endOfDay(), false],
            'this_week' => [$period, $now->copy()->startOfWeek(), $now->copy()->endOfWeek(), false],
            'last_week' => [$period, $now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek(), false],
            'last_month' => [$period, $now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth(), false],
            'this_year' => [$period, $now->copy()->startOfYear(), $now->copy()->endOfYear(), false],
            'previous_year' => [$period, $now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear(), false],
            default => ['this_month', $now->copy()->startOfMonth(), $now->copy()->endOfMonth(), false],
        };
    }

    private function periodOptions(): array
    {
        return [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 days',
            'last_30_days' => 'Last 30 days',
            'this_week' => 'This week',
            'last_week' => 'Last week',
            'this_month' => 'This month',
            'last_month' => 'Last month',
            'this_year' => 'This year',
            'previous_year' => 'Previous year',
        ];
    }

    private function previousWindow(Carbon $start, Carbon $end): array
    {
        $duration = $start->diffInSeconds($end) + 1;
        $previousEnd = $start->copy()->subSecond();

        return [
            'start' => $previousEnd->copy()->subSeconds($duration - 1),
            'end' => $previousEnd,
        ];
    }

    private function uniqueVisitors(Collection $events): int
    {
        return $events
            ->map(fn (AnalyticsEvent $event) => $event->session_hash ?: $event->ip_address)
            ->filter()
            ->unique()
            ->count();
    }

    private function bucketSeries(Carbon $start, Carbon $end, Collection $views, EloquentCollection $orders, EloquentCollection $transactions): array
    {
        $useMonths = $start->diffInDays($end) > 45;
        $cursor = $useMonths ? $start->copy()->startOfMonth() : $start->copy()->startOfDay();
        $last = $useMonths ? $end->copy()->startOfMonth() : $end->copy()->startOfDay();
        $buckets = [];

        while ($cursor->lte($last)) {
            $key = $useMonths ? $cursor->format('Y-m') : $cursor->toDateString();
            $buckets[$key] = [
                'label' => $useMonths ? $cursor->format('M Y') : $cursor->format('d M'),
                'views' => 0,
                'orders' => 0,
                'revenue' => 0.0,
                'transactions' => 0,
            ];

            $useMonths ? $cursor->addMonthNoOverflow() : $cursor->addDay();
        }

        foreach ($views as $event) {
            $key = $useMonths ? $event->occurred_at?->format('Y-m') : $event->occurred_at?->toDateString();
            if ($key && isset($buckets[$key])) {
                $buckets[$key]['views']++;
            }
        }

        foreach ($orders as $order) {
            $key = $useMonths ? $order->created_at?->format('Y-m') : $order->created_at?->toDateString();
            if ($key && isset($buckets[$key])) {
                $buckets[$key]['orders']++;
                if ($order->status === 'DELIVERED') {
                    $buckets[$key]['revenue'] += (float) $order->total_amount;
                }
            }
        }

        foreach ($transactions as $transaction) {
            $key = $useMonths ? $transaction->created_at?->format('Y-m') : $transaction->created_at?->toDateString();
            if ($key && isset($buckets[$key])) {
                $buckets[$key]['transactions']++;
            }
        }

        return array_values($buckets);
    }

    private function topDimension(Collection $rows, string $field, string $emptyLabel, ?int $limit = null): Collection
    {
        $total = max(1, $rows->count());

        $summary = $rows
            ->groupBy(fn ($row) => trim((string) ($row->{$field} ?: $emptyLabel)))
            ->map(fn (Collection $group, string $label) => [
                'label' => $label,
                'total' => $group->count(),
                'percent' => ($group->count() / $total) * 100,
            ])
            ->sortByDesc('total');

        if ($limit !== null) {
            $summary = $summary->take($limit);
        }

        return $summary->values();
    }

    private function topProducts(Carbon $start, Carbon $end): Collection
    {
        if (!Schema::hasTable('order_items')) {
            return collect();
        }

        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereNotIn('orders.status', ['CANCELLED'])
            ->groupBy(
                'order_items.product_id',
                'products.slug',
                'products.title',
                'products.image',
                'products.thumbnail_path',
                'products.is_active',
                'order_items.title'
            )
            ->orderByDesc(DB::raw('SUM(order_items.quantity)'))
            ->get([
                'order_items.product_id',
                'products.slug as product_slug',
                DB::raw('products.image as image'),
                DB::raw('products.thumbnail_path as thumbnail_path'),
                DB::raw('products.is_active as is_active'),
                DB::raw('COALESCE(products.title, order_items.title) as title'),
                DB::raw('SUM(order_items.quantity) as sold'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
            ]);

        $maxSold = max(1, (int) $rows->max('sold'));

        return $rows
            ->map(fn ($row) => [
                'title' => (string) $row->title,
                'slug' => $row->product_slug ? (string) $row->product_slug : null,
                'image_url' => $this->productImageUrl($row->image ?: $row->thumbnail_path),
                'is_active' => (bool) $row->is_active,
                'sold' => (int) $row->sold,
                'revenue' => (float) $row->revenue,
                'progress' => min(100, max(8, ((int) $row->sold / $maxSold) * 100)),
            ])
            ->values();
    }

    private function productImageUrl(?string $path): string
    {
        if (!$path) {
            return asset('assets/images/product-placeholder.svg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::url($path);
    }
}
