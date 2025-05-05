<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PromotionController;
use app\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\CategoryController;


// PUBLIC ROUTES
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// PROTECTED ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        Log::info('User request received', ['user' => $request->user()]);
        return response()->json($request->user());
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

//PRODUCT ROUTES
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

//CART ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
});

//ORDER ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
});

//ADMIN ROUTES
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::apiResource('/admin/products', ProductController::class);
    Route::apiResource('/admin/categories', CategoryController::class);
    Route::apiResource('/admin/orders', AdminOrderController::class);
    Route::post('/admin/promotions', [PromotionController::class, 'store']);
});
