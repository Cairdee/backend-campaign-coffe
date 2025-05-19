<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cartItems = Cart::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item->product->price * $item->quantity;
        }

        // Buat order baru
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => $total,
        ]);

        // Simpan order items
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
            ]);
        }

        // Kosongkan cart
        Cart::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Checkout berhasil',
            'order_id' => $order->id,
            'total' => $total,
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Order::with('items.product')->where('user_id', $user->id)->get();

        return response()->json($orders);
    }

    public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|string|in:' . implode(',', [
            Order::STATUS_PENDING,
            Order::STATUS_SENDING,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
        ])
    ]);

    $order = Order::findOrFail($id);

    // Cek apakah order milik user yang sedang login
    if ($order->user_id !== $request->user()->id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $order->status = $request->status;
    $order->save();

    return response()->json(['message' => 'Order status updated', 'order' => $order]);
}

}
