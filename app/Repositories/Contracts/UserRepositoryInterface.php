<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getAllUsers();

    public function getUserDetails(User $user);

    public function deleteUser(User $user);

    public function updateUser(User $user, array $data);
}
