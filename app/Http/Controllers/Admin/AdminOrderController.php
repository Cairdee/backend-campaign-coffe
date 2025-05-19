<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function getByStatus($status)
{
    $orders = Order::where('status', $status)->with('user')->latest()->get();
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

