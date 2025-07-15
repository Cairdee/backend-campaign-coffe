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
use App\Http\Controllers\NotificationController;

// PUBLIC ROUTES
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/google/token', [AuthController::class, 'loginWithGoogleToken']);
Route::get('/promotions', [PromotionController::class, 'index']);

// PROTECTED ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        Log::info('User request received', ['user' => $request->user()]);
        return response()->json($request->user());
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // CART ROUTES
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // ORDER ROUTES
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // PAYMENT ROUTES
    Route::post('/payment', [PaymentController::class, 'createPayment']);
});

// MIDTRANS CALLBACK
Route::post('/midtrans/callback', [PaymentController::class, 'handleCallback']);
Route::get('/payment/finish', [PaymentController::class, 'finish']);

// SHIPPING
Route::post('/calculate-ongkir', [ShippingController::class, 'calculateOngkir']);

// ADMIN
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard']);
     Route::get('orders', [AdminOrderController::class, 'index']);
    Route::get('orders/completed', [AdminOrderController::class, 'completedOrders']);
    Route::get('orders/status/{status}', [AdminOrderController::class, 'filterByStatus']);
    Route::get('orders/{id}', [AdminOrderController::class, 'show']);
    Route::put('orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
    Route::delete('orders/{id}', [AdminOrderController::class, 'destroy']);
    Route::put('orders/{id}/restore', [AdminOrderController::class, 'restore']);
    Route::delete('orders/{id}/force', [AdminOrderController::class, 'forceDelete']);
    Route::get('orders/{id}/invoice', [AdminOrderController::class, 'generateInvoice']);
    Route::get('/earnings', [AdminController::class, 'earnings']);
    Route::get('/history', [AdminController::class, 'orderHistory']);
    Route::get('pickup-orders/pending', [AdminOrderController::class, 'pickupList']);
    Route::get('pickup-orders/in-progress', [AdminOrderController::class, 'pickupInProgress']);
    Route::get('pickup-orders/completed', [AdminOrderController::class, 'pickupCompleted']);


    Route::apiResource('products', AdminProductController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('promotions', PromotionController::class)->except(['update', 'show']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});

//NOTIFICATIONS
Route::middleware('auth:sanctum')->get('/notifications', [NotificationController::class, 'index']);

