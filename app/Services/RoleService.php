<?php

namespace App\Services;

use App\Models\User;

class RoleService
{
    public function assignRoleForUser(int $id, string $role): bool|string
    {
        $userId = $id;
        $user = User::find($userId);

        if ($user->hasRole($role)) {
            return 'has role';
        }

        if (! $user) {
            return false;
        }

        $user->assignRole($role);

        return true;
    }

    public function revokeRoleForUser(int $id, string $role): bool|string
    {
        $userId = $id;
        $user = User::find($userId);

        if (! $user->hasRole($role)) {
            return 'has not role';
        }
        if (! $user) {
            return false;
        }
        $user->removeRole($role);

        return true;
    }
}
