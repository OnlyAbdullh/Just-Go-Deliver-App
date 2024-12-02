<?php

namespace App\Services;

use App\Repositories\OTPRepository;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPMail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class OTPService
{
    protected $otpRepository;

    public function __construct(OTPRepository $otpRepository)
    {
        $this->otpRepository = $otpRepository;
    }

    public function sendOTP(string $email): void
    {
        //  $otp = Str::padLeft(rand(0, 999999), 6, '0');
        $otp = strval(rand(100000, 999999));

        $expiresAt = Carbon::now()->addMinutes(5);

        session(['otp' => $otp, 'otp_expiry' => $expiresAt]);

        Mail::to($email)->send(new OTPMail($otp));
    }


    public function validateOTP(string $inputOtp): array
    {
        $sessionOtp = session('otp');
        $otpExpiry = session('otp_expiry');

        if (!$sessionOtp || Carbon::now()->isAfter($otpExpiry)) {
            return [
                'successful' => false,
                'message' => 'OTP has expired, please Resend it',
                'status_code' => 401,
            ];
        }

        if (trim((string)$sessionOtp) !== trim($inputOtp)) {
            return [
                'successful' => false,
                'message' => 'Invalid OTP.',
                'status_code' => 400,
            ];
        }

        return [
            'successful' => true,
            'message' => 'OTP is valid.',
            'status_code' => 200,
        ];
    }
}
