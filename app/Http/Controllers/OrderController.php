<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Midtrans\Snap;
use Midtrans\Config;
use App\Notifications\OrderStatusUpdated;
use App\Http\Traits\ResponseApi;

class OrderController extends Controller
{
    use ResponseApi;
    public function checkout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->success(['message' => 'Unauthorized'], 401);
        }

        $cartItems = Cart::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return $this->success(['message' => 'Cart is empty'], 400);
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
            'payment_method' => $request->payment_method, // Sudah benar
            'order_type' => $request->order_type ?? 'pickup', // TAMBAHKAN INI
            'address' => $request->address,
        ]);

        // Simpan order items
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
                'sugar' => $item->sugar,
                'temperature' => $item->temperature,
            ]);
        }

        // Kosongkan cart
        Cart::where('user_id', $user->id)->delete();

        // Konfigurasi Midtrans
        Config::$serverKey ='SB-Mid-server-ccARlZqoUIwNOPum96aVepYX';
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

        return $this->success([
            'message' => 'Checkout berhasil',
            'order_id' => $order->id,
            'total' => $total,
        ]);
    }

    public function index(Request $request)
{
    $user = $request->user();
    $orders = Order::with('items.product')->where('user_id', $user->id)->get();

    $orders = $orders->map(function ($order) {
        return [
            'id' => $order->id,
            'total_price' => $order->total_price,
            'status' => $order->status,
            'order_type' => $order->order_type,
            'payment_method' => $order->payment_method,
            'created_at' => $order->created_at,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product ? $item->product->name : null,
                    'product_image' => $item->product ? $item->product->image : null,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'sugar' => $item->sugar,
                    'temperature' => $item->temperature,
                ];
            }),
        ];
    });

    return $this->success($orders);
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
        return $this->success(['message' => 'Unauthorized'], 403);
    }

    // 4. Update status
    $order->status = $validated['status'];
    $order->save();

    // 5. Kirim notifikasi
    $order->user->notify(new OrderStatusUpdated($order));

    // 6. Return response
    return $this->success(['message' => 'Order status updated', 'order' => $order]);
}

public function show(Request $request, $id)
{
    $user = $request->user();
    $order = Order::with('items.product')->where('user_id', $user->id)->find($id);

    if (!$order) {
        return $this->success(['message' => 'Order not found'], 404);
    }

    return $this->success($order);
}
}
