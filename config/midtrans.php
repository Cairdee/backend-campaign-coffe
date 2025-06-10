

<?php

use Illuminate\Support\Facades\Log;

return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => false, // true untuk produksi
];
        // Update order status based on payment status
        if ($payload->transaction_status === 'capture') {
            if ($payload->fraud_status === 'accept') {
                $order->status = 'paid';
            } else {
                $order->status = 'pending';
            }
        } elseif ($payload->transaction_status === 'settlement') {
            $order->status = 'paid';
        } elseif ($payload->transaction_status === 'deny') {
            $order->status = 'failed';
        } elseif ($payload->transaction_status === 'expire') {
            $order->status = 'expired';
        } elseif ($payload->transaction_status === 'cancel') {
            $order->status = 'canceled';
        }

        $order->save();

        Log::info('Payment callback processed', ['order_id' => $orderId, 'status' => $order->status]);

        return response()->json(['message' => 'Payment processed successfully']);





