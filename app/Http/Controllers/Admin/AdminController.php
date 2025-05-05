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
        $totalSales = Order::sum('total_price');
        $totalOrders = Order::count();
        $totalProducts = Product::count();

        return response()->json([
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_products' => $totalProducts,
        ]);
    }
}
