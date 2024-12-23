<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    public function assignRole(User $user): bool
    {
        return $user->hasRole('manager');
    }

    public function revokeRole(User $user): bool
    {
        return $user->hasRole('manager');
    }
}
