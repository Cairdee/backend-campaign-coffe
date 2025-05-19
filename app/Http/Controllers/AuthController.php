<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = User::where('email', $request->email)->first();

    // Tambahkan pengecekan akun Google
    if ($user && $user->google_id && !$user->password) {
        return response()->json(['message' => 'Please use Google login'], 403);
    }

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'token'   => $token,
        'user'    => $user,
    ]);
}


    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $otp = $this->otpService->generateOtp($request->email);

        return response()->json(['message' => 'OTP sent successfully', 'otp' => $otp]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string',
        ]);

        $isValid = $this->otpService->verifyOtp($request->email, $request->otp);

        if (!$isValid) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        return response()->json(['message' => 'OTP verified successfully']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function redirectToGoogle()
{
    try {
        return Socialite::driver('google')->stateless()->redirect();
    } catch (\Exception $e) {
        Log::error('Google Redirect Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'message' => 'Google login failed',
            'error' => $e->getMessage(),
        ], 500);
    }
}
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            Log::info('Google ID Token:', ['id_token' => $googleUser->id_token]);
            Log::info('Access Token:', ['token' => $googleUser->token]);

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name'      => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                    'password'  => Hash::make(Str::random(24)),
                ]
            );

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Google login successful',
                'token'   => $token,
                'user'    => $user,
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Callback Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Google authentication failed', 'error' => $e->getMessage()], 500);
        }
    }
public function loginWithGoogleToken(Request $request)
{
    $request->validate([
        'token' => 'required|string',
    ]);

    try {
        // Verifikasi token ke Google
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->token,
        ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Invalid Google token'], 401);
        }

        $googleUser = $response->json();

        // Simpan atau update user
        $user = User::updateOrCreate(
            ['email' => $googleUser['email']],
            [
                'name'      => $googleUser['name'] ?? 'Unknown',
                'google_id' => $googleUser['sub'],
                'avatar'    => $googleUser['picture'] ?? null,
                'password'  => Hash::make(Str::random(24)),
            ]
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Google login successful',
            'token'   => $token,
            'user'    => $user,
        ]);

    } catch (\Exception $e) {
        Log::error('Google Token Login Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['message' => 'Google authentication failed', 'error' => $e->getMessage()], 500);
    }
}

}
