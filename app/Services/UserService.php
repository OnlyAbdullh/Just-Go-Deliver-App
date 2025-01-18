<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers()
    {
        return $this->userRepository->getAllUsers();
    }

    public function getUserDetails(User $user)
    {
        return $this->userRepository->getUserDetails($user);
    }

    public function deleteUser(User $user)
    {
        return $this->userRepository->deleteUser($user);
    }

    public function updateUser(User $user, array $data)
    {
        return $this->userRepository->updateUser($user, $data);
    }
}
