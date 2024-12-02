<?php

namespace App\Http\Controllers;


use App\Helpers\ApiResponse;
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
    /**
     * @OA\Post(
     *     path="/resend-otp",
     *     summary="Resend the OTP to the user's email during registration",
     *     tags={"OTP"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="user@example.com", description="The email address used for registration.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP resent successfully."),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Session expired.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Session expired. Please register again."),
     *             @OA\Property(property="status_code", type="integer", example=422)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error occurred.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *             @OA\Property(property="status_code", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function ResendOTP(Request $request)
    {
        $email=$request->input('email');
        $registrationData = session('registration_data');

        if (!$registrationData) {
            return ApiResponse::errorResponse(
                'Session expired. Please register again.',
                [],
                422
            );
        }

        $this->otpService->sendOTP($email);

        session(['otp_expiry' => now()->addMinutes(5)]);

        return ApiResponse::successResponse('OTP resent successfully.');
    }
    /**
     * @OA\Post(
     *     path="/validate-otp",
     *     summary="Validate the OTP provided by the user during registration",
     *     tags={"OTP"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="otp", type="string", example="123456", description="The OTP sent to the user's email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP validated and registration completed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registration completed successfully."),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="OTP has expired.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="OTP has expired, please Resend it."),
     *             @OA\Property(property="status_code", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid OTP."),
     *             @OA\Property(property="status_code", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Session expired.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Session expired. Please register again."),
     *             @OA\Property(property="status_code", type="integer", example=422)
     *         )
     *     )
     * )
     */

    public function validateOTP(Request $request)
    {
        $inputOtp = $request->input('otp');
        $registrationData = session('registration_data');

        if (!$registrationData) {
            return ApiResponse::errorResponse(
                'Session expired. Please register again.',
                [],
                422
            );
        }

        $otpValidationResult = $this->otpService->validateOTP($inputOtp);

        if (!$otpValidationResult['successful']) {

            return ApiResponse::errorResponse(
                $otpValidationResult['message'],
                [],
                $otpValidationResult['status_code']
            );
        }

        $this->authService->completeRegistration($registrationData);

        return ApiResponse::successResponse('Registration completed successfully.');
    }

}
