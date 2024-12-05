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
        return $this->userRepository->getAllUsers(20);
    }

    public function getUserDetails(User $user)
    {
        return $this->userRepository->getUserDetails($user);
    }

}
