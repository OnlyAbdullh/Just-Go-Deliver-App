<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthRepository implements AuthRepositoryInterface
{
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function createAccessToken(User $user, $deviceId): string
    {
        //  return JWTAuth::claims(['exp' => Carbon::now()->addMinutes(15)->timestamp])->fromUser($user);
        return JWTAuth::fromUser($user, ['fcm_token' => $deviceId]);
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

    public function findRefreshTokenByDevice($userId, $deviceId): ?object
    {
        return DB::table('user_refresh_tokens')
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    public function deviceExists(string $deviceId, int $userId): bool
    {
        return DB::table('user_refresh_tokens')
            ->where('device_id', $deviceId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function findFcmToken(int $userId, string $fcmToken)
    {
        return DB::table('device_tokens')
            ->where('user_id', $userId)
            ->where('fcm_token', $fcmToken)
            ->first();
    }

    public function fcmTokenExists(int $userId, string $fcmToken): bool
    {
        return DB::table('device_tokens')
            ->where('user_id', $userId)
            ->where('fcm_token', $fcmToken)
            ->exists();
    }

    public function saveFcmToken(int $userId, string $fcmToken)
    {
        DB::table('device_tokens')->updateOrInsert(
            ['user_id' => $userId, 'fcm_token' => $fcmToken],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    public function deleteFcmToken(string $fcmToken, int $userId)
    {
        DB::table('device_tokens')
            ->where('user_id', $userId)
            ->where('fcm_token', $fcmToken)
            ->delete();
    }
}
