<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminOrderController extends Controller
{
     // 1. Semua Pesanan
    public function index()
    {
         $orders = Order::with(['user', 'product'])
        ->where('order_type', 'delivery')
        ->latest()->get();

    return response()->json($orders);
    }

    // 2. Detail Pesanan
    public function show($id)
    {
        $order = Order::with(['user', 'product'])->findOrFail($id);
        return response()->json($order);
    }

    // 3. Ubah Status Pesanan
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Status updated', 'order' => $order]);
    }

    // 4. Soft Delete
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted (soft)']);
    }

    // 5. Filter Berdasarkan Status
    public function filterByStatus($status)
    {
         $orders = Order::with(['user', 'product'])
        ->where('order_type', 'delivery')
        ->where('status', $status)
        ->get();

    return response()->json($orders);
    }

    // 6. Lihat Pesanan Selesai
    public function completedOrders()
    {
         $orders = Order::with(['user', 'product'])
        ->where('order_type', 'delivery')
        ->whereIn('status', ['completed', 'delivered'])
        ->get();

    return response()->json($orders);
    }

    // 7. Restore Pesanan
    public function restore($id)
    {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->restore();

        return response()->json(['message' => 'Order restored']);
    }

    // 8. Force Delete
    public function forceDelete($id)
    {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->forceDelete();

        return response()->json(['message' => 'Order permanently deleted']);
    }
    // 1. Pickup List (belum diproses)
public function pickupList()
{
    $orders = Order::with(['user', 'product'])
        ->where('order_type', 'pickup')
        ->where('status', 'pending')
        ->latest()->get();

    return response()->json($orders);
}

// 2. In Progress Pickup
public function pickupInProgress()
{
    $orders = Order::with(['user', 'product'])
        ->where('order_type', 'pickup')
        ->where('status', 'processing')
        ->latest()->get();

    return response()->json($orders);
}

// 3. Completed Pickup
public function pickupCompleted()
{
    $orders = Order::with(['user', 'product'])
        ->where('order_type', 'pickup')
        ->whereIn('status', ['completed', 'delivered'])
        ->latest()->get();

    return response()->json($orders);
}


    public function generateInvoice($id)
{
    $order = Order::with('user', 'items.product')->findOrFail($id); // pastikan relasi 'items' dan 'product' ada
    $pdf = Pdf::loadView('admin.invoice', compact('order'));

    return $pdf->stream('invoice_order_' . $order->id . '.pdf'); // untuk preview
    // return $pdf->download('invoice_order_' . $order->id . '.pdf'); // untuk download langsung
}

}

