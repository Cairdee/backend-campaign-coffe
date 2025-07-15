<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseApi;

class AdminController extends Controller
{

    use ResponseApi;
    public function dashboard()
    {
        $totalSales = Order::where('status', 'completed')->sum('total_price');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalProducts = Product::count();

        return $this->success([
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

    $query = Order::with(['user', 'items.product'])->where('status', 'completed');

    if ($month) {
        $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]);
    }

    $orders = $query->orderBy('created_at', 'desc')->get();

    $ordersFormatted = $orders->map(function ($order) {
        return [
            'id' => $order->id,
            'customer_name' => $order->user ? $order->user->name : 'Customer',
            'total_price' => (float) $order->total_price,
            'status' => $order->status,
            'order_type' => $order->order_type,
            'created_at' => $order->created_at->format('Y-m-d H:i'),
            'items' => $order->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product ? $item->product->name : '',
                    'product_image' => $item->product ? $item->product->image : null,
                    'price' => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                ];
            }),
        ];
    });

    $totalEarnings = $orders->sum('total_price');

    return $this->success([
        'total' => (float) $totalEarnings,
        'orders' => $ordersFormatted,
    ]);
}

public function orderHistory()
{
    $orders = Order::with(['user', 'items.product'])
        ->whereIn('status', ['completed', 'delivered'])
        ->orderBy('created_at', 'desc')
        ->get();

    $ordersFormatted = $orders->map(function ($order) {
        return [
            'id' => $order->id,
            'customer_name' => $order->user ? $order->user->name : 'Customer',
            'total_price' => (float) $order->total_price,
            'status' => $order->status,
            'order_type' => $order->order_type,
            'created_at' => $order->created_at->format('Y-m-d H:i'),
            'items' => $order->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product ? $item->product->name : '',
                    'product_image' => $item->product ? $item->product->image : null,
                    'price' => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                ];
            }),
        ];
    });

    return $this->success($ordersFormatted);
}
    public function earningsByMonth()
    {
        $earnings = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as total")
            ->where('status', 'completed')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        return $this->success($earnings);

    }

}
