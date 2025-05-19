<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShippingController;



// PUBLIC ROUTES
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

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
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

});

//ADMIN ROUTES
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard']);
    Route::get('orders', [AdminOrderController::class, 'index']);
    Route::get('orders/status/{status}', [AdminOrderController::class, 'getByStatus']); // Tambahan
    Route::post('orders/{id}/status', [AdminOrderController::class, 'updateStatus']);

    // Route to generate invoice
     Route::get('orders/{id}/invoice', [AdminOrderController::class, 'generateInvoice']);

    Route::apiResource('products', AdminProductController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('promotions', PromotionController::class)->except(['update', 'show']);
});

// PAYMENT ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payment', [PaymentController::class, 'createPayment']);
});
Route::post('/midtrans/callback', [PaymentController::class, 'handleCallback']);

// SHIPPING ROUTES
Route::post('/calculate-ongkir', [ShippingController::class, 'calculateOngkir']);

//Google Routes
Route::post('/auth/google/token', [AuthController::class, 'loginWithGoogleToken']);







