<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseApi;

class PromotionController extends Controller
{
    use ResponseApi;
    public function index() {
        return $this->success(Promotion::all());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string',
            'image' => 'nullable|string',
        ]);

        $promotion = Promotion::create($validated);
        return $this->success($promotion, 201);
    }

    public function destroy($id) {
        Promotion::destroy($id);
        return $this->success(['message' => 'Promotion deleted']);
    }
}

