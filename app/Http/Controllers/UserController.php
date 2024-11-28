<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(RegisterUserRequest $request)
    {
        $user = $this->userService->register($request->validated());

        return response()->json([
            "status" => true,
            "message" => "User registered successfully",
            "user" => $user
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $tokens = $this->userService->login($credentials);

        if ($tokens) {
            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully',
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token']
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->header('Refresh-Token');

        if (!$refreshToken) {
            return response()->json([
                'status' => false,
                'message' => 'Refresh token is missing',
            ], 400);
        }

        $tokens = $this->userService->refresh($refreshToken);

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

    public function logout()
    {
        $this->userService->logout();

        return response()->json([
            "status" => true,
            "message" => "User logged out successfully"
        ]);
    }
}
