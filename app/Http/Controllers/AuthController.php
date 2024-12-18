<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\TemporaryRegistration;
use App\Services\AuthService;
use App\Services\OTPService;
use Illuminate\Http\Request;
use App\Jobs\SendOtpEmailJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    protected $userService;
    protected $otpService;

    public function __construct(AuthService $userService, OTPService $otpService)
    {
        $this->userService = $userService;
        $this->otpService = $otpService;
    }

    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Initiate user registration and send OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="securepassword123"),
     *             @OA\Property(property="location", type="string", example="New York, USA"),
     *             @OA\Property(property="phone_number", type="string", example="+1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration initiated. OTP sent to your email.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registration initiated. OTP sent to your email."),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *    @OA\Response(
     *          response=409,
     *          description="Email already registered",
     *          @OA\JsonContent(
     *              @OA\Property(property="successful", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="This email is already registered."),
     *              @OA\Property(property="status_code", type="integer", example=409)
     *          )
     *      ),
     *         @OA\Response(
     *          response=500,
     *          description="Unexpected error occurred.",
     *          @OA\JsonContent(
     *              @OA\Property(property="successful", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="An unexpected error occurred"),
     *              @OA\Property(property="status_code", type="integer", example=500)
     *          )
     *      ),
     * )
     */

    public function register(Request $request)
    {
        if (DB::table('users')->where('email', $request->input('email'))->exists()) {
            return JsonResponseHelper::errorResponse('This email is already registered.', [], 409);
        }
        $email = $request->input('email');

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'profile_' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('profiles', $imageName, 'local');
            $imageUrl = asset(Storage::url($imagePath));
        }

        TemporaryRegistration::where('email', $email)->delete();

        TemporaryRegistration::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $email,
            'password' => $request->input('password'),
            'location' => $request->input('location'),
            'phone_number' => $request->input('phone_number'),
            'image' => $imageUrl,
        ]);

        $this->otpService->sendOTP($email);
        return JsonResponseHelper::successResponse('Registration initiated. OTP sent to your email.');
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Authenticate user and generate tokens",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="securepassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User logged in successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="refresh_token", type="string", example="eyJpdiI6IkZVNlF2ZVVIZ25JWkVqaXgxUmFRRHc9PS..."),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=26),
     *                     @OA\Property(property="first_name", type="string", example="abdullah"),
     *                     @OA\Property(property="last_name", type="string", example="alkasm"),
     *                     @OA\Property(property="email", type="string", example="abdallaalksm9@gmail.com"),
     *                     @OA\Property(property="location", type="string", example="location 1"),
     *                     @OA\Property(property="image", type="string", nullable=true, example="any path"),
     *                     @OA\Property(property="role", type="string", example="user"),
     *                     @OA\Property(property="fcm_token", type="string", nullable=true, example=3231),
     *                     @OA\Property(property="phone_number", type="string", example="0969090711")
     *                 )
     *             ),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials"),
     *             @OA\Property(property="status_code", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="User already logged in on this device",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You have already logged in on this device."),
     *             @OA\Property(property="status_code", type="integer", example=409)
     *         )
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="Unexpected error occurred.",
     *          @OA\JsonContent(
     *              @OA\Property(property="successful", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="An unexpected error occurred"),
     *              @OA\Property(property="status_code", type="integer", example=500)
     *          )
     *      ),
     * )
     */




    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $deviceId = $request->header('Device-ID');

        $result = $this->userService->login($credentials, $deviceId);

        if ($result['successful']) {
            return JsonResponseHelper::successResponse(
                'User logged in successfully',
                [
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                    'user' => $result['user']
                ],
            );
        }

        return JsonResponseHelper::errorResponse(
            $result['message'],
            [],
            $result['status_code']
        );
    }

    /**
     * @OA\Post(
     *     path="/refresh",
     *     summary="Refresh the access token using a refresh token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=false,
     *         description="No body required. The headers must include 'Refresh-Token' and 'Device-ID'."
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Access token refreshed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Access token refreshed successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR..."),
     *                 @OA\Property(property="expires_in", type="string", example="15 m")
     *             ),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid or expired refresh token.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid or expired refresh token"),
     *             @OA\Property(property="status_code", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Device ID not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Device ID not found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error occurred.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred"),
     *             @OA\Property(property="status_code", type="integer", example=500)
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="Refresh-Token",
     *         in="header",
     *         required=true,
     *         description="The refresh token for authentication",
     *         @OA\Schema(type="string", example="dGhpc2lzYXJlZnJlc2h0b2tlbg==")
     *     ),
     *     @OA\Parameter(
     *         name="Device-ID",
     *         in="header",
     *         required=true,
     *         description="The device ID associated with the user session",
     *         @OA\Schema(type="string", example="1234567890abcdef")
     *     )
     * )
     */


    public function refresh(Request $request)
    {
        $refreshToken = $request->header('Refresh-Token');
        $deviceId = $request->header('Device-ID');

        try {
            $tokens = $this->userService->refreshToken($refreshToken, $deviceId);
            return JsonResponseHelper::successResponse('Access token refreshed successfully.', ['access_token' => $tokens['access_token'], 'expires_in' => '1 hour']);

        } catch (\Exception $e) {
            return JsonResponseHelper::errorResponse($e->getMessage(), [], $e->getCode());
        }
    }


    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Logout a user and invalidate tokens",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="Device-ID",
     *         in="header",
     *         required=true,
     *         description="The device ID associated with the user's session",
     *         @OA\Schema(type="string", example="1234567890abcdef")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User logged out successfully."),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Device ID not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Device ID not found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
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



    public function logout(Request $request)
    {
        $deviceId = $request->header('Device-ID');

        try {
            $this->userService->logout($deviceId);
            return JsonResponseHelper::successResponse('User logged out successfully.');

        } catch (\Exception $e) {
            return JsonResponseHelper::errorResponse(
                $e->getMessage(),
                [],
                404
            );
        }
    }
}
