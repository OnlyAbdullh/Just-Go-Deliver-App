<?php
namespace App\Repositories;

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
class UserRepository implements UserRepositoryInterface
{
    public function createUser(array $data):  User
    {
        return User::create($data);
    }
    public function findByEmail(string $email): ? User
    {
        return User::where('email', $email)->first();
    }
    public function createAccessToken(User $user): string
    {
        return JWTAuth::claims(['exp' => Carbon::now()->addMinutes(15)->timestamp])->fromUser($user);
    }
    public function createRefreshToken(): string
    {
        $randomBytes = random_bytes(128);

        $refreshToken = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($randomBytes));

        return Crypt::encryptString($refreshToken);
    }
    public function saveRefreshToken(User $user, string $deviceId, string $refreshToken, $expiresAt): void
    {
        DB::table('user_refresh_tokens')->updateOrInsert(
            [
                'user_id' => $user->id,
                'device_id' => $deviceId,
            ],
            [
                'refresh_token' => $refreshToken,
                'expires_at' => $expiresAt,
                'updated_at' => now(),
            ]
        );
    }

    public function findRefreshToken(string $refreshToken, string $deviceId): ?object
    {
       // $decryptedToken = Crypt::decryptString($refreshToken);

        return DB::table('user_refresh_tokens')
            ->where('refresh_token', $refreshToken)
            ->where('device_id', $deviceId)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function deleteRefreshToken(string $deviceId, int $userId): void
    {
        DB::table('user_refresh_tokens')
            ->where('device_id', $deviceId)
            ->where('user_id', $userId)
            ->delete();
    }
    public function deleteAllRefreshTokens(int $userId): void
    {
        DB::table('user_refresh_tokens')
            ->where('user_id', $userId)
            ->delete();
    }
    public function findRefreshTokenByDevice($userId, $deviceId)
    {
        return DB::table('user_refresh_tokens')
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

}
