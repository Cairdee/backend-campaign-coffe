<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseApi;

class AdminProductController extends Controller
{
    use ResponseApi;
    public function index() {
        return $this->success(Product::all());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'image' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer',
        ]);
        $validated['rating'] = $request->rating ?? 0;
        $validated['review_count'] = $request->review_count ?? 0;


        $product = Product::create($validated);
        return $this->success($product, 201);
    }

    public function show($id) {
        return $this->success(Product::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes',
            'description' => 'nullable',
            'price' => 'sometimes|numeric',
            'image' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'stock' => 'sometimes|integer',
        ]);
        $product->update($validated);
        return $this->success($product);
    }

    public function destroy($id) {
        Product::destroy($id);
        return $this->success(['message' => 'Product deleted']);
    }
}
