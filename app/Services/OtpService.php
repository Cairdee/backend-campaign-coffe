<?php

namespace App\Services;

use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class OtpService
{
    public function generateOtp($email)
{
    try {
        // Validasi email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid email format");
        }

        // Pastikan user ada
        if (!User::where('email', $email)->exists()) {
            throw new \Exception("User not found");
        }

        $otp = rand(100000, 999999);

        // Simpan OTP dengan transaction
        DB::beginTransaction();
        try {
            $otpRecord = Otp::updateOrCreate(
                ['email' => $email],
                [
                    'otp' => $otp,
                    'expires_at' => now()->addMinutes(5),
                ]
            );

            if (!$otpRecord) {
                throw new \Exception("Failed to save OTP");
            }

            // Kirim email
            Mail::to($email)->send(new OtpMail($otp));

            DB::commit();
            return $otp;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    } catch (\Exception $e) {
        Log::error('OTP Error: '.$e->getMessage()."\n".$e->getTraceAsString());
        throw new \Exception("OTP generation failed: ".$e->getMessage());
    }
}

    public function verifyOtp($email, $otp)
    {
        $record = Otp::where('email', $email)
                    ->where('otp', $otp)
                    ->where('expires_at', '>', now())
                    ->first();

        if ($record) {
            $record->delete();
            return true;
        }

        return false;
    }
}
