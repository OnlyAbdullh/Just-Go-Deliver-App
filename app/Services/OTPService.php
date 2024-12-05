<?php

namespace App\Services;

use App\Jobs\SendOtpEmailJob;
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

        $expiresAt = Carbon::now()->addMinutes(2);

        session([
            "otp_{$email}" => $otp,
            "otp_expiry_{$email}" => $expiresAt
        ]);
        SendOtpEmailJob::dispatch($email, $otp);
    }


    public function validateOTP(string $inputOtp,string $email): array
    {
        $sessionOtp = session("otp_{$email}");
        $otpExpiry = session("otp_expiry_{$email}");

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
