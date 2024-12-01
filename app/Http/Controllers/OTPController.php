<?php

namespace App\Http\Controllers;


use App\Services\AuthService;
use App\Services\OTPService;
use Illuminate\Http\Request;
use App\Models\User;

class OTPController extends Controller
{
    protected $otpService;
    protected $authService;

    public function __construct(OTPService $otpService, AuthService $authService)
    {
        $this->otpService = $otpService;
        $this->authService = $authService;
    }

    public function sendOTP(string $email)
    {
        $registrationData = session('registration_data');

        if (!$registrationData) {
            return response()->json([
                'message' => 'Session expired. Please register again.'
            ], 422);
        }

        $this->otpService->sendOTP($email);

        // Update session expiry
        session(['otp_expiry' => now()->addMinutes(5)]);

        return response()->json(['message' => 'OTP resent successfully.']);
    }


    public function validateOTP(Request $request)
    {
        $inputOtp = $request->input('otp');
        //\Log::info($inputOtp);
        $registrationData = session('registration_data');
        // \Log::info($registrationData);

        if (!$registrationData) {
            return response()->json([
                'message' => 'Session expired. Please register again.'
            ], 422);
        }

        $isValid = $this->otpService->validateOTP($inputOtp, $registrationData['email']);

        if (!$isValid) {
            return response()->json(['message' => 'here: Invalid or expired OTP.'], 422);
        }

        $this->authService->completeRegistration($registrationData);

        return response()->json([
            'status' => true,
            'message' => 'Registration completed successfully.',
        ]);
    }

}
