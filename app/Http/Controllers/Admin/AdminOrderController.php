<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index() {
        return response()->json(Order::with('user')->latest()->get());
    }

    public function updateStatus(Request $request, $id) {
        $order = Order::findOrFail($id);
        $order->status = $request->input('status');
        $order->save();

        return response()->json(['message' => 'Order status updated', 'order' => $order]);
    }
}

