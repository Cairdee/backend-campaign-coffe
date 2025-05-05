<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() {
        return response()->json(Category::all());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    public function update(Request $request, $id) {
        $category = Category::findOrFail($id);
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy($id) {
        Category::destroy($id);
        return response()->json(['message' => 'Category deleted']);
    }
}

