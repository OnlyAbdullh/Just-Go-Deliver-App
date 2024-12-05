<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->getAllUsers();
        return response()->json($users);
    }

    public function show(User $user)
    {
        $userDetails = $this->userService->getUserDetails($user);
        return response()->json($userDetails);
    }

}
