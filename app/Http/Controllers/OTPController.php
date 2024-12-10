<?php

namespace App\Http\Controllers;


use App\Helpers\JsonResponseHelper;
use App\Models\TemporaryRegistration;
use App\Services\AuthService;
use App\Services\OTPService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Jobs\SendOtpEmailJob;

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
     *             required={"email"},
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
        $email = $request->input('email');

        $registrationData = TemporaryRegistration::where('email', $email)->first();

        if (!$registrationData) {
            return JsonResponseHelper::errorResponse(
                'Registration data not found or expired. Please register again.',
                [],
                422
            );
        }

        $this->otpService->sendOTP($email);

        return JsonResponseHelper::successResponse('OTP resent successfully.');
    }


    /**
     * @OA\Post(
     *     path="/validate-otp",
     *     summary="Validate OTP and complete user registration",
     *     tags={"OTP"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="otp", type="string", example="123456", description="The OTP sent to the user's email."),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="The email associated with the OTP.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP validated successfully, and registration completed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registration completed successfully."),
     *             @OA\Property(property="status_code", type="integer", example=200)
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
     *         description="Session expired or registration data missing.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Session expired. Please register again."),
     *             @OA\Property(property="status_code", type="integer", example=422)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred. Please try again later."),
     *             @OA\Property(property="status_code", type="integer", example=500)
     *         )
     *     ),
     * )
     */



    public function validateOTP(Request $request)
    {


        $email = $request->input('email');
        $inputOtp = $request->input('otp');

        $registrationData = TemporaryRegistration::where('email', $email)->first();

        if (!$registrationData) {
            return JsonResponseHelper::errorResponse(
                'Session expired or email not found. Please register again.',
                [],
                422
            );
        }

        $otpValidationResult = $this->otpService->validateOTP($inputOtp, $email);

        if (!$otpValidationResult['successful']) {
            return JsonResponseHelper::errorResponse(
                $otpValidationResult['message'],
                [],
                $otpValidationResult['status_code']
            );
        }

        $this->authService->completeRegistration($registrationData->toArray(), $email);

        $registrationData->delete();

        return JsonResponseHelper::successResponse('Registration completed successfully.');
    }



}
