<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseApi;

class CategoryController extends Controller
{
    use ResponseApi;
    public function index() {
        return $this->success(Category::all());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $category = Category::create($validated);
        return $this()->success($category, 201);
    }

    public function update(Request $request, $id) {
        $category = Category::findOrFail($id);
        $category->update($request->all());
        return $this->success($category);
    }

    public function destroy($id) {
        Category::destroy($id);
        return $this->success(['message' => 'Category deleted']);
    }
}

