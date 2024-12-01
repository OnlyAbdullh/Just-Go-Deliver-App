<?php

namespace App\Http\Controllers;


use App\Services\OTPService;
use Illuminate\Http\Request;
use App\Models\User;

class OTPController extends Controller
{
    protected $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function sendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $this->otpService->sendOTP($request->email);

        return response()->json(['message' => 'OTP sent successfully to your email.']);
    }

    public function validateOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);

        $isValid = $this->otpService->validateOTP($request->email, $request->otp);

        if (!$isValid) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        return response()->json(['message' => 'OTP validated successfully.']);
    }
}
