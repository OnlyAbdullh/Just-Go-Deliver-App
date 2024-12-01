<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\OTPService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $userService;
    protected $otpService;

    public function __construct(AuthService $userService, OTPService $otpService)
    {
        $this->userService = $userService;
        $this->otpService = $otpService;
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        $registrationData = [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'location' => $request->input('location'),
            'phone_number' => $request->input('phone_number'),
        ];
      //  \Log::info('Before  ', session()->all());

        session([
            'registration_data' => $registrationData,
            'otp_expiry' => now()->addMinutes(5)
        ]);
       // \Log::info('After ', session()->all());
        $this->otpService->sendOTP($validatedData['email']);

        return response()->json([
            "status" => true,
            "message" => "Registration initiated. OTP sent to your email.",
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $deviceId = $request->header('Device-ID');

        if (!$deviceId) {
            return response()->json([
                'status' => false,
                'message' => 'Device ID is required',
            ], 400);
        }

        $result = $this->userService->login($credentials, $deviceId);

        if ($result['status']) {
            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully',
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'user' => $result['user'],
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => $result['message']
        ], $result['status_code']);
    }


    public function refresh(Request $request)
    {
        $refreshToken = $request->header('Refresh-Token');
        $deviceId = $request->header('Device-ID');

        if (!$refreshToken || !$deviceId) {
            return response()->json([
                'status' => false,
                'message' => 'Refresh token or Device ID is missing',
            ], 400);
        }

        $tokens = $this->userService->refreshToken($refreshToken, $deviceId);

        if ($tokens) {
            return response()->json([
                'status' => true,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'message' => 'Access token refreshed successfully',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Refresh token is invalid or expired',
        ], 401);
    }


    public function logout(Request $request)
    {
        $deviceId = $request->header('Device-ID');

        if (!$deviceId) {
            return response()->json([
                'status' => false,
                'message' => 'Device ID is required'
            ], 400);
        }

        try {
            $this->userService->logout($deviceId);

            return response()->json([
                'status' => true,
                'message' => 'User logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
}
