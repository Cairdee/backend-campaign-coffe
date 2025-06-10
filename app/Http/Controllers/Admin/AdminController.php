<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalSales = Order::where('status', 'completed')->sum('total_price');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalProducts = Product::count();

        return response()->json([
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
            'total_products' => $totalProducts,
        ]);
    }
    public function earnings(Request $request)
{
    $month = $request->input('month'); // format: 2025-06

    $query = Order::where('status', 'completed');

    if ($month) {
        $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]);
    }

    $orders = $query
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($order) {
            return [
                'orderName' => $order->customer_name ?? 'Customer',
                'orderItems' => implode(', ', json_decode($order->items ?? '[]')),
                'price' => $order->total_price,
                'created_at' => $order->created_at->format('Y-m-d'),
            ];
        });

    $totalEarnings = $query->sum('total_price');

    return response()->json([
        'total' => $totalEarnings,
        'orders' => $orders,
    ]);
}
    public function orderHistory()
{
    // Ambil semua order dengan status completed atau delivered
    $orders = Order::whereIn('status', ['completed', 'delivered'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($order) {
            return [
                'order_name' => $order->customer_name ?? 'Customer',
                'order_items' => implode(', ', json_decode($order->items ?? '[]')),
                'price' => $order->total_price,
                'created_at' => $order->created_at->format('Y-m-d H:i'),
            ];
        });

    return response()->json([
        'data' => $orders,
    ]);
}
    public function earningsByMonth()
{
    $earnings = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as total")
        ->where('status', 'completed')
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->get();

    return response()->json($earnings);

}

}
