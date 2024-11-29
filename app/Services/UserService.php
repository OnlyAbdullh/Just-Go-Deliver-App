<?php
namespace App\Services;

use App\Repositories\UserRepositoryInterface;
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

    public function logout()
    {
        $user = auth()->user();
        $this->userRepository->saveRefreshToken($user, null, null);
        auth()->logout();
        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function login(array $credentials)
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if ($user && Hash::check($credentials['password'], $user->password)) {

            $accessToken = $this->userRepository->createAccessToken($user);

            $refreshToken = $this->userRepository->createRefreshToken($user);
            $this->userRepository->saveRefreshToken($user, $refreshToken, Carbon::now()->addWeeks(2));
            return [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'bearer',
                'expires_in' => 15 * 60,
            ];
        }

        return null;
    }


    public function refreshToken(string $refreshToken): array
    {
        $user = $this->userRepository->findByRefreshToken($refreshToken);

        if (!$user) {
            throw new \Exception('Invalid or expired refresh token', 401);
        }

        $accessToken = JWTAuth::claims(['exp' => Carbon::now()->addMinutes(15)->timestamp])->fromUser($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => 15 * 60,
        ];
    }

}
