<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // 1. Generate Snap Token
    public function createPayment(Request $request)
{
    $validated = $request->validate([
        'order_id' => 'required|exists:orders,id',
    ]);

    $order = Order::with('user')->findOrFail($validated['order_id']);

    if ($order->user_id !== $request->user()->id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Cek apakah sudah ada midtrans_order_id, kalau belum buat dan simpan
    if (!$order->midtrans_order_id) {
        $order->midtrans_order_id = 'ORDER-' . $order->id . '-' . time();
        $order->save();
    }

    $params = [
        'transaction_details' => [
            'order_id' => $order->midtrans_order_id,
            'gross_amount' => $order->total_price,
        ],
        'customer_details' => [
            'first_name' => $order->user->name,
            'email' => $order->user->email,
        ]
    ];

    $snapToken = Snap::getSnapToken($params);

    return response()->json(['snap_token' => $snapToken]);
}



    // 2. Handle Callback
    public function handleCallback(Request $request)
    {
        $payload = json_decode($request->getContent());

        $orderIdParts = explode('-', $payload->order_id);
        $orderId = $orderIdParts[1] ?? null;

        if (!$orderId) {
            return response()->json(['message' => 'Invalid order ID'], 400);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (in_array($payload->transaction_status, ['capture', 'settlement'])) {
            $order->status = 'paid';
        } elseif (in_array($payload->transaction_status, ['deny', 'cancel', 'expire'])) {
            $order->status = 'cancelled';
        } elseif ($payload->transaction_status === 'pending') {
            $order->status = 'pending';
        }

        $order->save();

        Log::info('Midtrans callback received', ['payload' => $payload]);

        return response()->json(['message' => 'Callback processed']);
    }
}
