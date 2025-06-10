<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Midtrans\Snap;
use Midtrans\Config;
use App\Notifications\OrderStatusUpdated;

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
            'status' => Order::STATUS_PENDING,
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

        // Konfigurasi Midtrans
        Config::$serverKey = 'YOUR_MIDTRANS_SERVER_KEY';
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $midtransOrderId = $order->id . '-' . uniqid();

        $transaction = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => $total,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ];

        // Buat transaksi Midtrans
        $snap = Snap::createTransaction($transaction);

        return response()->json([
            'message' => 'Checkout berhasil',
            'order_id' => $order->id,
            'snap_url' => $snap->redirect_url,
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
    // 1. Validasi status
    $validated = $request->validate([
        'status' => 'required|string|in:' . implode(',', [
            Order::STATUS_PENDING,
            Order::STATUS_SENDING,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
        ])
    ]);

    // 2. Ambil order
    $order = Order::findOrFail($id);

    // 3. Cek apakah order milik user ini
    if ($order->user_id !== $request->user()->id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // 4. Update status
    $order->status = $validated['status'];
    $order->save();

    // 5. Kirim notifikasi
    $order->user->notify(new OrderStatusUpdated($order));

    // 6. Return response
    return response()->json(['message' => 'Order status updated', 'order' => $order]);
}
}
