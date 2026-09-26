<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\View\View;

/**
 * Every number on this dashboard comes from a real aggregate query against
 * the same tables the storefront writes to — there is no mock or seeded
 * "demo analytics" data path. Revenue only counts orders with
 * status = 'paid' (see Order::STATUS_PAID), which only ever gets set by
 * the Stripe webhook (StripeWebhookController) once payment is actually
 * confirmed — so this dashboard can't be inflated by abandoned or
 * failed-payment orders sitting in 'pending'/'payment_failed'.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $totalRevenueCents = Order::where('status', Order::STATUS_PAID)->sum('total_cents');
        $paidOrderCount = Order::where('status', Order::STATUS_PAID)->count();
        $averageOrderCents = $paidOrderCount > 0 ? intdiv($totalRevenueCents, $paidOrderCount) : 0;

        $ordersToday = Order::whereDate('created_at', today())->count();
        $pendingOrders = Order::where('status', Order::STATUS_PENDING)->count();

        // Revenue for each of the last 14 days, including days with zero
        // paid orders — built from a fixed date range rather than only the
        // dates present in the table, so the chart doesn't silently skip
        // quiet days.
        $revenueRows = Order::query()
            ->where('status', Order::STATUS_PAID)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(total_cents) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $revenueByDay = collect(range(13, 0))->map(function (int $daysAgo) use ($revenueRows) {
            $date = now()->subDays($daysAgo)->format('Y-m-d');
            return [
                'date' => now()->subDays($daysAgo)->format('M j'),
                'cents' => (int) ($revenueRows[$date] ?? 0),
            ];
        });

        // Top products by units sold, from order_items — not from cart
        // activity, so a product added to many carts but never actually
        // bought correctly doesn't rank here.
        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', Order::STATUS_PAID)
            ->select('order_items.product_name')
            ->selectRaw('SUM(order_items.quantity) as units_sold')
            ->selectRaw('SUM(order_items.unit_price_cents * order_items.quantity) as revenue_cents')
            ->groupBy('order_items.product_name')
            ->orderByDesc('units_sold')
            ->limit(6)
            ->get();

        // Anything at or below 5 units — an operational alert, not a
        // marketing "low stock" badge.
        $lowStockVariants = ProductVariant::query()
            ->with('product')
            ->where('stock_count', '<=', 5)
            ->orderBy('stock_count')
            ->limit(10)
            ->get();

        $recentOrders = Order::with('user')->latest()->limit(8)->get();

        return view('admin.dashboard', [
            'totalRevenueCents' => $totalRevenueCents,
            'paidOrderCount' => $paidOrderCount,
            'averageOrderCents' => $averageOrderCents,
            'ordersToday' => $ordersToday,
            'pendingOrders' => $pendingOrders,
            'revenueByDay' => $revenueByDay,
            'topProducts' => $topProducts,
            'lowStockVariants' => $lowStockVariants,
            'recentOrders' => $recentOrders,
        ]);
    }
}
