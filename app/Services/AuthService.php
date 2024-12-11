<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    protected $authRepository;
    protected $roleService;

    public function __construct(AuthRepository $authRepository, RoleService $roleService)
    {
        $this->authRepository = $authRepository;
        $this->roleService = $roleService;
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->authRepository->createUser($data);
    }

    public function completeRegistration($registrationData, string $email)
    {
        if (!$registrationData) {
            return response()->json([
                "status" => false,
                "message" => "Session expired. Please register again.",
            ], 422);
        }

        $user = $this->register($registrationData);

        $this->roleService->assignRoleForUser($user->id, 'user');

        return response()->json([
            "status" => true,
            "message" => "Registration completed successfully.",
        ], 200);
    }


    public function logout(string $deviceId)
    {
        $user = auth()->user();
        $deviceExists = $this->authRepository->deviceExists($deviceId, $user->id);
        if (!$deviceExists) {
            throw new \Exception('Device ID not found', 404);
        }
        $currentToken = JWTAuth::getToken();
        DB::transaction(function () use ($currentToken, $deviceId, $user) {
            DB::table('token_blacklist')->insert([
                'token' => $currentToken,
                'expires_at' => Carbon::now()->addMinutes(config('jwt.ttl')),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->authRepository->deleteRefreshToken($deviceId, $user->id);
        });

        auth()->logout();
    }


    public function login(array $credentials, string $deviceId)
    {
        $user = $this->authRepository->findByEmail($credentials['email']);

        if ($user && Hash::check($credentials['password'], $user->password)) {

            // Check if the user is already logged in on this device by looking for the device's refresh token
            $existingRefreshToken = $this->authRepository->findRefreshTokenByDevice($user->id, $deviceId);

            if ($existingRefreshToken) {
                return [
                    'successful' => false,
                    'message' => 'You have already logged in on this device.',
                    'status_code' => 409
                ];
            }

            $accessToken = $this->authRepository->createAccessToken($user);
            $refreshToken = $this->authRepository->createRefreshToken();

            $this->authRepository->saveRefreshToken($user, $deviceId, $refreshToken, Carbon::now()->addWeeks(2));
            return [
                'successful' => true,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'bearer',
                'expires_in' => 15 * 60,
                'user' => $user,
            ];
        }
        return [
            'successful' => false,
            'message' => 'Invalid credentials',
            'status_code' => 401
        ];
    }

    public function refreshToken(string $refreshToken, string $deviceId): array
    {
        $refreshTokenRecord = $this->authRepository->findRefreshToken($refreshToken, $deviceId);

        if (!$refreshTokenRecord) {
            throw new \Exception('Invalid or expired refresh token', 401);
        }
        $user = User::find($refreshTokenRecord->user_id);

        $deviceExists = $this->authRepository->deviceExists($deviceId, $user->id);

        if (!$deviceExists) {
            throw new \Exception('Device ID not found', 404);
        }
        $accessToken = $this->authRepository->createAccessToken($user);

        return [
            'access_token' => $accessToken,
        ];
    }


}
