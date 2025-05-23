<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        Log::info('Getting cart items for user', [
            'user_id' => $userId,
            'token' => $request->bearerToken(),
            'request_headers' => $request->headers->all()
        ]);

        $carts = Cart::with('product')->where('user_id', $userId)->get();
        Log::info('Cart items retrieved', [
            'user_id' => $userId,
            'cart_count' => $carts->count(),
            'cart_items' => $carts->toArray()
        ]);

        return CartResource::collection($carts);
    }

    public function store(Request $request)
    {
        Log::info('Adding item to cart', [
            'user_id' => $request->user()->id,
            'request_data' => $request->all(),
            'token' => $request->bearerToken()
        ]);

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
