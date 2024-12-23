<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\TemporaryRegistration;
use App\Models\User;
use App\Services\AuthService;
use App\Services\OTPService;
use Illuminate\Http\Request;

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
     *     summary="Resend OTP to the user's email",
     *     tags={"OTP"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="The email address of the user")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP resent successfully.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Registration data not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Registration data not found. Please register again.")
     *         )
     *     )
     * )
     */
    public function ResendOTP(Request $request)
    {
        $email = $request->input('email');

        $registrationData = TemporaryRegistration::where('email', $email)->first();

        if (! $registrationData) {
            return JsonResponseHelper::errorResponse(
                __('messages.registration_data_not_found'),
                [],
                422
            );
        }

        $this->otpService->sendOTP($email);

        return JsonResponseHelper::successResponse(__('messages.otp_resent'));
    }

    /**
     * @OA\Post(
     *     path="/validate-otp",
     *     summary="Validate OTP and complete user registration",
     *     tags={"OTP"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "otp"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="The email address of the user"),
     *             @OA\Property(property="otp", type="string", example="123456", description="The OTP sent to the user's email")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Registration completed successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registration completed successfully.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Email not found or session expired",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Email not found. Please register again.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="OTP is invalid.")
     *         )
     *     )
     * )
     */
    public function validateOTP(Request $request)
    {

        $email = $request->input('email');
        $inputOtp = $request->input('otp');

        $registrationData = TemporaryRegistration::where('email', $email)->first();

        if (! $registrationData) {
            return JsonResponseHelper::errorResponse(
                __('messages.email_not_found'),
                [],
                422
            );
        }

        $otpValidationResult = $this->otpService->validateOTP($inputOtp, $email);

        if (! $otpValidationResult['successful']) {
            return JsonResponseHelper::errorResponse(
                $otpValidationResult['message'],
                [],
                $otpValidationResult['status_code']
            );
        }

        $this->authService->completeRegistration($registrationData->toArray());

        $registrationData->delete();

        return JsonResponseHelper::successResponse(__('messages.registration_completed'));
    }
}
