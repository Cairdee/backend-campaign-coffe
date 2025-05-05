<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index() {
        return response()->json(Promotion::all());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string',
            'image' => 'nullable|string',
        ]);

        $promotion = Promotion::create($validated);
        return response()->json($promotion, 201);
    }

    public function destroy($id) {
        Promotion::destroy($id);
        return response()->json(['message' => 'Promotion deleted']);
    }
}

