<?php
namespace App\Repositories;

namespace App\Repositories;

use App\Models\User;

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
}
