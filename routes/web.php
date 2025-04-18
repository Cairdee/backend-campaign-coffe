<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuthController;

// Route test kirim email
Route::get('/test-email', function () {
    Mail::raw('Ini email test dari Laravel menggunakan Gmail!', function ($message) {
        $message->to('jovancojojo268@gmail.com')
                ->subject('Tes Kirim Email');
    });

    return 'Email berhasil dikirim!';
});

// Google Auth Route dengan middleware web
Route::middleware('web')->group(function () {
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});
