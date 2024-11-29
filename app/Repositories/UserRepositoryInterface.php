<?php
namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function createUser(array $data): User;
    public function findByEmail(string $email): ?User;
    public function createAccessToken(User $user): string;
    public function createRefreshToken(): string;
    public function saveRefreshToken(User $user, string $deviceId, string $refreshToken, $expiresAt): void;
    public function findRefreshToken(string $refreshToken, string $deviceId): ?object;
    public function deleteRefreshToken(string $deviceId, int $userId): void;
    public function deleteAllRefreshTokens(int $userId): void;
    public function findRefreshTokenByDevice(int $userId, string $deviceId);
}
