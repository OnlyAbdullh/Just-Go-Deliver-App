<?php

namespace App\Services;

use App\Helpers\JsonResponseHelper;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\AuthRepository;
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

    public function completeRegistration($registrationData)
    {
        if (! $registrationData) {
            // return JsonResponseHelper::errorResponse(__('messages.session_expired', [], 422));
            return response()->json([
                'status' => false,
                'message' => __('messages.session_expired'),
            ], 422);
        }

        $user = $this->register($registrationData);

        $this->roleService->assignRoleForUser($user->id, 'user');

        return JsonResponseHelper::successResponse(__('messages.registration_completed'));
    }

    public function logout(string $fcmToken)
    {
        $user = auth()->user();
        $deviceExists = $this->authRepository->fcmTokenExists($user->id, $fcmToken);

        if (! $deviceExists) {
            throw new \Exception(__('messages.device_token_not_found'), 404);
        }

        $currentToken = JWTAuth::getToken();
        if (! $currentToken) {
            throw new \Exception(__('messages.no_token_provided'), 400);
        }

        $decodedToken = JWTAuth::getPayload($currentToken)->toArray();

        if (($decodedToken['fcm_token'] ?? null) !== $fcmToken) {
            throw new \Exception(__('messages.access_token_mismatch'), 403);
        }

        DB::transaction(function () use ($currentToken, $fcmToken, $user) {
            DB::table('token_blacklist')->insert([
                'token' => $currentToken,
                'expires_at' => now()->addMinutes(config('jwt.ttl')),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->authRepository->deleteRefreshToken($fcmToken, $user->id);
            $this->authRepository->deleteFcmToken($fcmToken, $user->id);
        });

        auth()->logout();
    }

    public function login(array $credentials, string $fcmToken)
    {
        $user = $this->authRepository->findByEmail($credentials['email']);

        if ($user && Hash::check($credentials['password'], $user->password)) {

            $existingFcmToken = $this->authRepository->findFcmToken($user->id, $fcmToken);

            if ($existingFcmToken) {
                return [
                    'successful' => false,
                    'message' => __('messages.already_logged_in'),
                    'status_code' => 409,
                ];
            }

            $accessToken = JWTAuth::claims(['fcm_token' => $fcmToken])->fromUser($user);

            $refreshToken = $this->authRepository->createRefreshToken();

            $this->authRepository->saveFcmToken($user->id, $fcmToken);

            $this->authRepository->saveRefreshToken(
                $user,
                $fcmToken,
                $refreshToken,
                Carbon::now()->addWeeks(2)
            );

            return [
                'successful' => true,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'user' => new UserResource($user),
            ];
        }

        return [
            'successful' => false,
            'message' => __('messages.invalid_credentials'),
            'status_code' => 401,
        ];
    }

    public function refreshToken(string $refreshToken, string $deviceId): array
    {
        $refreshTokenRecord = $this->authRepository->findRefreshToken($refreshToken, $deviceId);

        if (! $refreshTokenRecord) {
            throw new \Exception(__('messages.invalid_or_expired_refresh_token'), 401);
        }
        $user = User::find($refreshTokenRecord->user_id);
        if (! $user) {
            throw new \Exception(__('messages.user_not_found'), 404);
        }
        $deviceExists = $this->authRepository->deviceExists($deviceId, $user->id);

        if (! $deviceExists) {
            throw new \Exception(__('messages.device_id_not_found'), 404);
        }
        $accessToken = $this->authRepository->createAccessToken($user, $deviceId);

        return [
            'access_token' => $accessToken,
            'expires_in' => __('messages.one_hour'),
        ];
    }
}
