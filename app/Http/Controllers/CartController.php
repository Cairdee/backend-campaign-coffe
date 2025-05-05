<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $carts = Cart::with('product')->where('user_id', $userId)->get();

        return response()->json($carts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $userId = $request->user()->id;

        $cart = Cart::updateOrCreate(
            ['user_id' => $userId, 'product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );

        return response()->json(['message' => 'Cart updated', 'data' => $cart]);
    }

    public function destroy($id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }
}
