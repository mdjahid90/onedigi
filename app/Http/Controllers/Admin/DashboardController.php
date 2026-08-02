<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\AnalyticsEvent;
use App\Models\DailyVisit;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totalProducts = Product::query()->count();
        $totalUsers = User::query()->count();
        $totalPayments = Transaction::query()->count();
        $pendingPayments = Transaction::query()->whereIn('status', ['PENDING', 'CREATED', 'INITIATED'])->count();
        $unpaidInvoices = Order::query()->whereIn('status', ['PENDING', 'PROCESSING'])->count();

        $ordersByStatus = Order::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $totalOrders = (int) ($ordersByStatus->sum() ?? 0);
        $ordersPending = (int) ($ordersByStatus->get('PENDING') ?? 0);
        $ordersProcessing = (int) ($ordersByStatus->get('PROCESSING') ?? 0);
        $ordersDelivered = (int) ($ordersByStatus->get('DELIVERED') ?? 0);

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $prevMonthStart = (clone $monthStart)->subMonth();
        $prevMonthEnd = (clone $monthStart)->subSecond();

        $activeUsers = Schema::hasColumn('users', 'last_seen_at')
            ? User::query()->where('last_seen_at', '>=', now()->subMinutes(15))->count()
            : 0;

        $revenueToday = (float) Order::query()
            ->where('status', 'DELIVERED')
            ->where('created_at', '>=', $today)
            ->sum('total_amount');

        $totalRevenue = (float) Order::query()
            ->where('status', 'DELIVERED')
            ->sum('total_amount');

        $revenueThisMonth = (float) Order::query()
            ->where('status', 'DELIVERED')
            ->where('created_at', '>=', $monthStart)
            ->sum('total_amount');

        $deliveredOrdersThisMonth = (int) Order::query()
            ->where('status', 'DELIVERED')
            ->where('created_at', '>=', $monthStart)
            ->count();

        $deliveredOrdersPrevMonth = (int) Order::query()
            ->where('status', 'DELIVERED')
            ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
            ->count();

        $visitorsThisMonth = 0;
        $visitorsPrevMonth = 0;

        if (Schema::hasTable('daily_visits')) {
            try {
                $visitorsThisMonth = (int) DailyVisit::query()
                    ->where('date', '>=', $monthStart->toDateString())
                    ->sum('visitors');

                $visitorsPrevMonth = (int) DailyVisit::query()
                    ->whereBetween('date', [$prevMonthStart->toDateString(), $prevMonthEnd->toDateString()])
                    ->sum('visitors');
            } catch (QueryException $exception) {
                report($exception);
            }
        }

        $conversionRate = $visitorsThisMonth > 0
            ? ($deliveredOrdersThisMonth / $visitorsThisMonth) * 100
            : 0.0;

        $conversionRatePrev = $visitorsPrevMonth > 0
            ? ($deliveredOrdersPrevMonth / $visitorsPrevMonth) * 100
            : 0.0;

        $conversionRateDelta = $conversionRate - $conversionRatePrev;

        $monthlyFinanceSeries = collect();
        $monthlyTransactionStatistics = collect();
        $monthlyUnpaidInvoiceSeries = collect();
        $monthlyCustomerSeries = collect();
        $monthlyOrderSeries = collect();
        $expenseStatuses = ['FAILED', 'CANCELLED', 'REFUNDED', 'CHARGEBACK'];

        for ($i = 5; $i >= 0; $i--) {
            $periodStart = Carbon::now()->startOfMonth()->subMonths($i);
            $periodEnd = (clone $periodStart)->endOfMonth();

            $monthlyRevenue = (float) Order::query()
                ->where('status', 'DELIVERED')
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->sum('total_amount');

            $monthlyExpenses = (float) Transaction::query()
                ->whereIn('status', $expenseStatuses)
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->sum('amount');

            $monthlyFinanceSeries->push([
                'label' => $periodStart->format('M'),
                'revenue' => round($monthlyRevenue, 2),
                'profit' => round($monthlyRevenue - $monthlyExpenses, 2),
                'expenses' => round($monthlyExpenses, 2),
            ]);

            $monthlyTransactionStatistics->push([
                'label' => $periodStart->format('M'),
                'total' => Transaction::query()->whereBetween('created_at', [$periodStart, $periodEnd])->count(),
                'completed' => Transaction::query()->where('status', 'COMPLETED')->whereBetween('created_at', [$periodStart, $periodEnd])->count(),
                'pending' => Transaction::query()->whereIn('status', ['PENDING', 'CREATED', 'INITIATED'])->whereBetween('created_at', [$periodStart, $periodEnd])->count(),
            ]);

            $monthlyUnpaidInvoiceSeries->push([
                'label' => $periodStart->format('M'),
                'total' => Order::query()
                    ->whereIn('status', ['PENDING', 'PROCESSING'])
                    ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ->count(),
            ]);

            $monthlyCustomerSeries->push([
                'label' => $periodStart->format('M'),
                'total' => User::query()
                    ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ->count(),
            ]);

            $monthlyOrderSeries->push([
                'label' => $periodStart->format('M'),
                'total' => Order::query()
                    ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ->count(),
                'delivered' => Order::query()
                    ->where('status', 'DELIVERED')
                    ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ->count(),
            ]);
        }

        $gatewayStatistics = Transaction::query()
            ->select('gateway', DB::raw('COUNT(*) as total'))
            ->groupBy('gateway')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'gateway' => $row->gateway ?: 'Unknown',
                'total' => (int) $row->total,
            ])
            ->values();

        $topProducts = $this->topProducts($monthStart, $prevMonthStart, $prevMonthEnd);
        $recentOrders = Order::query()->latest()->take(5)->get();

        return view('admin.dashboard', [
            'activeSubscriptions' => $this->activeSubscriptions(),
            'activeUsers' => $activeUsers,
            'dailyTrafficSeries' => $this->dailyTrafficSeries(),
            'locationSeries' => $this->locationSeries(),
            'realtimeMetrics' => $this->realtimePayload(),
            'recentAdminNotifications' => $this->recentAdminNotifications(),
            'totalProducts' => $totalProducts,
            'totalUsers' => $totalUsers,
            'totalPayments' => $totalPayments,
            'pendingPayments' => $pendingPayments,
            'unpaidInvoices' => $unpaidInvoices,
            'unreadAdminNotifications' => $this->unreadAdminNotifications(),
            'unreadContactMessages' => $this->unreadContactMessages(),
            'openSupportTickets' => $this->openSupportTickets(),
            'totalOrders' => $totalOrders,
            'ordersPending' => $ordersPending,
            'ordersProcessing' => $ordersProcessing,
            'ordersDelivered' => $ordersDelivered,
            'deliveredOrdersThisMonth' => $deliveredOrdersThisMonth,
            'revenueToday' => $revenueToday,
            'totalRevenue' => $totalRevenue,
            'revenueThisMonth' => $revenueThisMonth,
            'conversionRate' => $conversionRate,
            'conversionRateDelta' => $conversionRateDelta,
            'conversionRateTrendPositive' => $conversionRateDelta >= 0,
            'visitorsThisMonth' => $visitorsThisMonth,
            'monthlyFinanceSeries' => $monthlyFinanceSeries->values()->all(),
            'monthlyTransactionStatistics' => $monthlyTransactionStatistics->values()->all(),
            'monthlyUnpaidInvoiceSeries' => $monthlyUnpaidInvoiceSeries->values()->all(),
            'monthlyCustomerSeries' => $monthlyCustomerSeries->values()->all(),
            'monthlyOrderSeries' => $monthlyOrderSeries->values()->all(),
            'gatewayStatistics' => $gatewayStatistics,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function realtime(): JsonResponse
    {
        return response()->json($this->realtimePayload());
    }

    private function topProducts(Carbon $monthStart, Carbon $prevMonthStart, Carbon $prevMonthEnd): Collection
    {
        $topProductsCurrent = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', 'DELIVERED')
            ->where('orders.created_at', '>=', $monthStart)
            ->groupBy('order_items.product_id', 'products.slug', 'products.title', 'products.image', 'products.thumbnail_path', 'order_items.title')
            ->orderByDesc(DB::raw('SUM(order_items.subtotal)'))
            ->get([
                'order_items.product_id',
                'products.slug as product_slug',
                DB::raw('COALESCE(products.image, products.thumbnail_path) as image_path'),
                DB::raw('COALESCE(products.title, order_items.title) as title'),
                DB::raw('SUM(order_items.quantity) as sales_count'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
            ]);

        $topProductIds = $topProductsCurrent->pluck('product_id')->filter()->unique()->values();
        $topProductsPrev = [];

        if ($topProductIds->count() > 0) {
            $topProductsPrev = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', 'DELIVERED')
                ->whereBetween('orders.created_at', [$prevMonthStart, $prevMonthEnd])
                ->whereIn('order_items.product_id', $topProductIds)
                ->groupBy('order_items.product_id')
                ->get([
                    'order_items.product_id',
                    DB::raw('SUM(order_items.quantity) as sales_count'),
                    DB::raw('SUM(order_items.subtotal) as revenue'),
                ])
                ->keyBy('product_id')
                ->all();
        }

        return $topProductsCurrent->map(static function ($row) use ($topProductsPrev) {
            $productId = $row->product_id;
            $prev = $productId !== null ? ($topProductsPrev[$productId] ?? null) : null;
            $prevRevenue = $prev ? (float) $prev->revenue : 0.0;
            $currentRevenue = (float) $row->revenue;

            if ($prevRevenue <= 0 && $currentRevenue > 0) {
                $growthPct = 100.0;
            } elseif ($prevRevenue <= 0) {
                $growthPct = 0.0;
            } else {
                $growthPct = (($currentRevenue - $prevRevenue) / $prevRevenue) * 100;
            }

            return [
                'product_id' => $productId,
                'product_slug' => $row->product_slug !== null ? (string) $row->product_slug : null,
                'image_path' => $row->image_path !== null ? (string) $row->image_path : null,
                'title' => (string) $row->title,
                'sales_count' => (int) $row->sales_count,
                'revenue' => (float) $row->revenue,
                'growth_pct' => $growthPct,
            ];
        })->values();
    }

    private function dailyTrafficSeries(): array
    {
        $start = now()->subDays(29)->startOfDay();
        $days = collect(range(0, 29))->mapWithKeys(function ($i) use ($start) {
            $date = $start->copy()->addDays($i);
            return [$date->toDateString() => [
                'label' => $date->format('d M'),
                'web' => 0,
                'mobile' => 0,
                'total' => 0,
            ]];
        });

        if (Schema::hasTable('analytics_events')) {
            AnalyticsEvent::query()
                ->where('event_type', 'page_view')
                ->where('occurred_at', '>=', $start)
                ->get(['occurred_at', 'device_type'])
                ->each(function (AnalyticsEvent $event) use ($days) {
                    $date = $event->occurred_at?->toDateString();
                    if (!$date || !$days->has($date)) {
                        return;
                    }

                    $row = $days->get($date);
                    $device = $event->device_type ?: 'desktop';
                    if (in_array($device, ['mobile', 'tablet'], true)) {
                        $row['mobile']++;
                    } else {
                        $row['web']++;
                    }
                    $row['total']++;
                    $days->put($date, $row);
                });
        } elseif (Schema::hasTable('daily_visits')) {
            DailyVisit::query()
                ->where('date', '>=', $start->toDateString())
                ->get(['date', 'visitors'])
                ->each(function (DailyVisit $visit) use ($days) {
                    $date = $visit->date?->toDateString();
                    if ($date && $days->has($date)) {
                        $row = $days->get($date);
                        $row['web'] = (int) $visit->visitors;
                        $row['total'] = (int) $visit->visitors;
                        $days->put($date, $row);
                    }
                });
        }

        return $days->values()->all();
    }

    private function realtimePayload(): array
    {
        $today = Carbon::today();
        $activeUsers = Schema::hasColumn('users', 'last_seen_at')
            ? User::query()->where('last_seen_at', '>=', now()->subMinutes(15))->count()
            : 0;

        $viewsToday = 0;
        $visitorsToday = 0;
        $viewsLastMinute = 0;

        if (Schema::hasTable('analytics_events')) {
            $todayViews = AnalyticsEvent::query()
                ->where('event_type', 'page_view')
                ->where('occurred_at', '>=', $today)
                ->get(['session_hash', 'ip_address', 'occurred_at']);

            $viewsToday = $todayViews->count();
            $viewsLastMinute = $todayViews
                ->filter(fn (AnalyticsEvent $event) => $event->occurred_at?->gte(now()->subMinute()))
                ->count();
            $visitorsToday = $todayViews
                ->map(fn (AnalyticsEvent $event) => $event->session_hash ?: $event->ip_address)
                ->filter()
                ->unique()
                ->count();
        }

        return [
            'active_users' => (int) $activeUsers,
            'active_users_percent' => User::query()->count() > 0
                ? round(($activeUsers / max(1, User::query()->count())) * 100)
                : 0,
            'views_today' => (int) $viewsToday,
            'views_last_minute' => (int) $viewsLastMinute,
            'visitors_today' => (int) $visitorsToday,
            'traffic' => $this->dailyTrafficSeries(),
            'locations' => $this->locationSeries()->values()->all(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function locationSeries(): Collection
    {
        if (Schema::hasTable('analytics_events') && Schema::hasColumn('analytics_events', 'country_name')) {
            $locations = AnalyticsEvent::query()
                ->selectRaw('UPPER(country_code) as code, COALESCE(country_name, country_code) as label, COUNT(*) as total')
                ->where('event_type', 'page_view')
                ->where('occurred_at', '>=', now()->subDays(30))
                ->where(function ($query) {
                    $query->whereNotNull('country_name')->orWhereNotNull('country_code');
                })
                ->groupBy('code', 'label')
                ->orderByDesc('total')
                ->limit(8)
                ->get()
                ->map(fn ($row) => [
                    'code' => $row->code ?: null,
                    'label' => $row->label ?: 'Unknown',
                    'total' => (int) $row->total,
                ]);

            if ($locations->isNotEmpty()) {
                return $locations;
            }
        }

        return Order::query()
            ->select('country', DB::raw('COUNT(*) as total'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'code' => null,
                'label' => $row->country ?: 'Unknown',
                'total' => (int) $row->total,
            ]);
    }

    private function activeSubscriptions(): int
    {
        return OrderItem::query()
            ->with(['order:id,status,created_at,completed_at', 'product.category:id,name'])
            ->whereHas('order', fn ($query) => $query->where('status', 'DELIVERED'))
            ->whereHas('product.category', fn ($query) => $query->where('name', 'like', '%subscription%'))
            ->whereHas('order', fn ($query) => $query->where('created_at', '>=', now()->subYear()))
            ->get()
            ->filter(function (OrderItem $item) {
                $start = $item->order?->completed_at ?: $item->order?->created_at;
                if (!$start) {
                    return false;
                }

                return $start->copy()->addDays($this->subscriptionDays($item))->isFuture();
            })
            ->count();
    }

    private function subscriptionDays(OrderItem $item): int
    {
        $meta = is_array($item->meta) ? $item->meta : [];
        $text = strtolower(json_encode($meta, JSON_UNESCAPED_UNICODE) ?: '');

        if (preg_match('/(\d+)\s*(year|yr)/', $text, $match)) {
            return max(1, (int) $match[1]) * 365;
        }

        if (preg_match('/(\d+)\s*(month|mo)/', $text, $match)) {
            return max(1, (int) $match[1]) * 30;
        }

        if (preg_match('/(\d+)\s*(day|days)/', $text, $match)) {
            return max(1, (int) $match[1]);
        }

        return 30;
    }

    private function unreadAdminNotifications(): int
    {
        return Schema::hasTable('admin_notifications')
            ? AdminNotification::query()->whereNull('read_at')->count()
            : 0;
    }

    private function recentAdminNotifications(): Collection
    {
        return Schema::hasTable('admin_notifications')
            ? AdminNotification::query()->latest()->take(5)->get()
            : collect();
    }

    private function unreadContactMessages(): int
    {
        return Schema::hasTable('contact_messages')
            ? ContactMessage::query()->whereNull('read_at')->count()
            : 0;
    }

    private function openSupportTickets(): int
    {
        return Schema::hasTable('support_tickets')
            ? SupportTicket::query()->where('status', 'open')->count()
            : 0;
    }
}
