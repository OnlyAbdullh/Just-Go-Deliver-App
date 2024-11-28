<?php
namespace App\Repositories;

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
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
    public function findByRefreshToken(string $refreshToken): ?User
    {
        $decryptedToken = Crypt::decryptString($refreshToken);

        return User::where('refresh_token', $decryptedToken)
            ->where('refresh_token_expires_at', '>', Carbon::now())
            ->first();
    }
    public function saveRefreshToken(User $user, ?string $refreshToken, $expiresAt): void
    {
        $user->update([
            'refresh_token' => $refreshToken,
            'refresh_token_expires_at' => $expiresAt,
        ]);
    }
}
