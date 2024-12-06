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
        $userDetails = User::select('id', 'first_name', 'last_name', 'email', 'location', 'phone_number', 'image', 'fcm_token')
            ->where('id', $user->id)
            ->first();

        if (!$userDetails) {
            return ['error' => 'User not found'];
        }

        $userArray = $userDetails->toArray();
        $userArray['role'] = $userDetails->roles->pluck('name')->first() ?? 'No Role';

        return $userArray;
    }


    public function deleteUser(User $user)
    {
        return $user->delete();
    }

    public function updateUser(User $user, array $data)
    {
        $user->fill($data)->save();
        return $user;
    }
}
