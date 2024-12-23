<?php

namespace App\Repositories;

use App\Http\Resources\UserResource;
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
        ])->paginate($perPage);

        return UserResource::collection($users);
    }

    public function getUserDetails(User $user)
    {
        $userDetails = User::with('roles')->find($user->id);

        if (! $userDetails) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return new UserResource($userDetails);
    }

    public function deleteUser(User $user)
    {
        return $user->delete();
    }

    public function updateUser(User $user, array $data)
    {
        $user->fill($data)->save();

        return new UserResource($user);
    }
}
