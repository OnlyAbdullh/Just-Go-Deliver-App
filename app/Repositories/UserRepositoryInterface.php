<?php
namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function createUser(array $data): User;
    public function findByEmail(string $email): ?User;
    public function createAccessToken(User $user): string;
    public function createRefreshToken( ): string;
}
