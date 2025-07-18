<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Traits\ResponseApi;

class AdminOrderController extends Controller
{
    use ResponseApi;

    // Semua Pesanan Delivery
    public function index()
    {
        $orders = Order::with(['user', 'items.product'])
            ->where('order_type', 'delivery')
            ->latest()->get();

        $orders = $orders->map(function ($order) {
            return $this->formatOrder($order);
        });

        return $this->success($orders);
    }

    // Detail Pesanan
    public function show($id)
    {
        $order = Order::with(['user', 'items.product'])->findOrFail($id);
        return $this->success($this->formatOrder($order));
    }

    // Ubah Status Pesanan
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return $this->success(['message' => 'Status updated', 'order' => $this->formatOrder($order)]);
    }

    // Soft Delete
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return $this->success(['message' => 'Order deleted (soft)']);
    }

    // Filter Berdasarkan Status
    public function filterByStatus($status)
    {
        $orders = Order::with(['user', 'items.product'])
            ->where('order_type', 'delivery')
            ->where('status', $status)
            ->get();

        $orders = $orders->map(function ($order) {
            return $this->formatOrder($order);
        });

        return $this->success($orders);
    }

    // Lihat Pesanan Selesai
    public function completedOrders()
    {
        $orders = Order::with(['user', 'items.product'])
            ->where('order_type', 'delivery')
            ->whereIn('status', ['completed', 'delivered'])
            ->get();

        $orders = $orders->map(function ($order) {
            return $this->formatOrder($order);
        });

        return $this->success($orders);
    }

    // Restore Pesanan
    public function restore($id)
    {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->restore();

        return $this->success(['message' => 'Order restored']);
    }

    // Force Delete
    public function forceDelete($id)
    {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->forceDelete();

        return $this->success(['message' => 'Order permanently deleted']);
    }

    // Pickup List (belum diproses)
    public function pickupList()
    {
        $orders = Order::with(['user', 'items.product'])
            ->where('order_type', 'pickup')
            ->where('status', 'pending')
            ->latest()->get();

        $orders = $orders->map(function ($order) {
            return $this->formatOrder($order);
        });

        return $this->success($orders);
    }

    // In Progress Pickup
    public function pickupInProgress()
    {
        $orders = Order::with(['user', 'items.product'])
            ->where('order_type', 'pickup')
            ->where('status', 'processing')
            ->latest()->get();

        $orders = $orders->map(function ($order) {
            return $this->formatOrder($order);
        });

        return $this->success($orders);
    }

    // Completed Pickup
    public function pickupCompleted()
    {
        $orders = Order::with(['user', 'items.product'])
            ->where('order_type', 'pickup')
            ->whereIn('status', ['completed', 'delivered'])
            ->latest()->get();

        $orders = $orders->map(function ($order) {
            return $this->formatOrder($order);
        });

        return $this->success($orders);
    }

    // Generate Invoice
    public function generateInvoice($id)
    {
        $order = Order::with('user', 'items.product')->findOrFail($id);
        $pdf = Pdf::loadView('admin.invoice', compact('order'));

        return $pdf->stream('invoice_order_' . $order->id . '.pdf');
    }

    // Helper untuk format order
    private function formatOrder($order)
{
    return [
        'id' => $order->id,
        'customer_name' => $order->user ? $order->user->name : null,
        'total_price' => (float) $order->total_price,
        'status' => $order->status,
        'order_type' => $order->order_type,
        'created_at' => $order->created_at->format('Y-m-d H:i'),
        'payment_method' => $order->payment_method, // <--- Tambahkan ini
        'location' => $order->address, // <--- Tambahkan ini
        'items' => $order->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product ? $item->product->name : null,
                'product_image' => $item->product ? $item->product->image : null,
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
            ];
        }),
    ];
}
}
