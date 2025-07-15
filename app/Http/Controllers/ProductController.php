<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Traits\ResponseApi;

class ProductController extends Controller
{
    use ResponseApi;
    public function index(Request $request)
    {
        $query = Product::with('category');


if ($request->has('category')) {
    $query->whereHas('category', function ($q) use ($request) {
        $q->where('name', $request->category);
    });
}


    // Search by name or description
    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%');
        });
    }

    return ProductResource::collection($query->get());
    }

    public function show($id)
    {
        return new ProductResource(Product::findOrFail($id));
    }

    public function store(Request $request)
{
    $request->validate([
        'name'          => 'required|string',
        'category_id'   => 'required|exists:categories,id',
        'price'         => 'required|numeric',
        'image'         => 'nullable|string',
        'description'   => 'nullable|string',
        'rating'        => 'nullable|numeric',
        'review_count'  => 'nullable|integer',
        'stock'         => 'required|integer',
    ]);

    $imagePath = $request->image
        ? 'storage/images/menu/' . $request->image
        : null;

    $product = Product::create([
        'name' => $request->name,
        'category_id' => $request->category_id,
        'price' => $request->price,
        'image' => $imagePath,
        'description' => $request->description,
        'rating' => $request->rating ?? 0,
        'review_count' => $request->review_count ?? 0,
        'stock' => $request->stock,
    ]);

    return $this->success([
        'message' => 'Product created successfully',
        'data' => $product
    ], 201);
}


public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $request->validate([
        'name'          => 'sometimes|string',
        'category_id'   => 'sometimes|exists:categories,id',
        'price'         => 'sometimes|numeric',
        'image'         => 'nullable|string',
        'description'   => 'nullable|string',
        'rating'        => 'nullable|numeric',
        'review_count'  => 'nullable|integer',
        'stock'         => 'sometimes|integer',
    ]);

    $imagePath = $request->image
        ? 'storage/images/menu/' . $request->image
        : $product->image;

    $product->update([
        'name' => $request->name ?? $product->name,
        'category_id' => $request->category_id ?? $product->category_id,
        'price' => $request->price ?? $product->price,
        'image' => $imagePath,
        'description' => $request->description ?? $product->description,
        'rating' => $request->rating ?? $product->rating,
        'review_count' => $request->review_count ?? $product->review_count,
        'stock' => $request->stock ?? $product->stock,
    ]);

    return $this->success([
        'message' => 'Product updated successfully',
        'data' => $product
    ]);
}


    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return $this->success(['message' => 'Product deleted successfully']);
    }
}

