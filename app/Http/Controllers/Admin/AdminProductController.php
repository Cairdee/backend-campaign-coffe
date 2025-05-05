<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function index() {
        return response()->json(Product::all());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'image' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    public function show($id) {
        return response()->json(Product::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json($product);
    }

    public function destroy($id) {
        Product::destroy($id);
        return response()->json(['message' => 'Product deleted']);
    }
}
