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
        $token = $this->userService->login($request->only(['email', 'password']));

        return response()->json([
            "status" => $token ? true : false,
            "message" => $token ? "User logged in successfully" : "Invalid details",
            "token" => $token
        ]);
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
