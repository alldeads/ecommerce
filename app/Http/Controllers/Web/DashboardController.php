<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with analytics.
     */
    public function index(): Response
    {
        $user = Auth::user();

        // Get orders per month for the last 6 months
        // Use different SQL based on database driver
        $driver = DB::getDriverName();
        $monthFormat = $driver === 'sqlite' 
            ? "strftime('%Y-%m', created_at)" 
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $ordersPerMonth = Order::where('user_id', $user->id)
            ->select(
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw("{$monthFormat} as month")
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'count' => $item->count,
                    'total' => number_format((float) $item->total, 2),
                ];
            });

        // Get overall statistics
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'total_spent' => number_format(
                Order::where('user_id', $user->id)->sum('total_amount'),
                2
            ),
            'orders_this_month' => Order::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'total_products' => Product::where('is_active', true)->count(),
        ];

        // Get recent orders
        $recentOrders = Order::where('user_id', $user->id)
            ->with('orderItems.product')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total_amount' => number_format((float) $order->total_amount, 2),
                    'created_at' => $order->created_at->format('M d, Y'),
                    'items_count' => $order->orderItems->count(),
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'ordersPerMonth' => $ordersPerMonth,
            'recentOrders' => $recentOrders,
        ]);
    }
}
