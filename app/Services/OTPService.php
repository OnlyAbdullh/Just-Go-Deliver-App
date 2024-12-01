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

        session(['otp' => (string)$otp, 'otp_expiry' => $expiresAt]);

        Mail::to($email)->send(new OTPMail($otp));
    }


    public function validateOTP($inputOtp, string $email): bool
    {
        $sessionOtp = session('otp');
        $otpExpiry = session('otp_expiry');

        if (!$sessionOtp || Carbon::now()->isAfter($otpExpiry)) {
            return false;
        }
       // \Log::info($sessionOtp);
       // \Log::info($inputOtp);
        if (trim((string)$sessionOtp) !== trim((string)$inputOtp)) {
            return false;
        }
        return true;
    }
}
