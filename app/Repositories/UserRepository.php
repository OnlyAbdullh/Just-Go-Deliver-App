<?php

namespace App\Repositories;

use App\Helpers\JsonResponseHelper;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function getAllUsers($perPage)
    {
        $users = User::select([
            'id',
            'first_name',
            'last_name',
            'email',
            'location',
            'phone_number',
            'image',
            'fcm_token',
        ])->paginate($perPage);
        return $users;
    }

    public function getUserDetails(User $user)
    {
        $userDetails = $user->select('id', 'first_name', 'last_name', 'email', 'location', 'phone_number', 'image', 'fcm_token')->first();

        return $userDetails->toArray();;
    }


}
