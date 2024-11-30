<?php
namespace App\Services;

use App\Models\TokenBlacklist;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepository->createUser($data);
    }

    public function logout(string $deviceId)
    {
        $user = auth()->user();
        $deviceExists = $this->userRepository->deviceExists($deviceId, $user->id);
        if (!$deviceExists) {
            throw new \Exception('Device ID not found', 404);
        }
        $currentToken = JWTAuth::getToken();
        DB::table('token_blacklist')->insert([
            'token' =>   $currentToken,
            'expires_at' => Carbon::now()->addMinutes(config('jwt.ttl')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->userRepository->deleteRefreshToken($deviceId, $user->id);
        auth()->logout();

    }


    public function login(array $credentials, string $deviceId)
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if ($user && Hash::check($credentials['password'], $user->password)) {

            // Check if the user is already logged in on this device by looking for the device's refresh token
            $existingRefreshToken = $this->userRepository->findRefreshTokenByDevice($user->id, $deviceId);

            if ($existingRefreshToken) {
                return [
                    'status' => false,
                    'message' => 'You have already logged in on this device.',
                    'status_code' => 409
                ];
            }

            $accessToken = $this->userRepository->createAccessToken($user);
            $refreshToken = $this->userRepository->createRefreshToken();

            $this->userRepository->saveRefreshToken($user, $deviceId, $refreshToken, Carbon::now()->addWeeks(2));

            return [
                'status' => true,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'bearer',
                'expires_in' => 15 * 60,
            ];
        }
        return [
            'status' => false,
            'message' => 'Invalid credentials',
            'status_code' => 401
        ];
    }

    public function refreshToken(string $refreshToken, string $deviceId): array
    {
        $refreshTokenRecord = $this->userRepository->findRefreshToken($refreshToken, $deviceId);

        if (!$refreshTokenRecord) {
            throw new \Exception('Invalid or expired refresh token', 401);
        }

        $user = User::find($refreshTokenRecord->user_id);
        $deviceExists = $this->userRepository->deviceExists($deviceId, $user->id);

        if (!$deviceExists) {
            throw new \Exception('Device ID not found', 404);
        }
        $accessToken = $this->userRepository->createAccessToken($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,  //ما عم حدثو
            'token_type' => 'bearer',
            'expires_in' => 15 * 60,
        ];
    }

}
