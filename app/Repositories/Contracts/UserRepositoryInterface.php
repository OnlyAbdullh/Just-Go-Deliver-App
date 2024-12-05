<?php
namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getAllUsers($perPage);
    public function getUserDetails(User $user);

}
