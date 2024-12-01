<?php

namespace App\Services;

use App\Models\User;

class RoleService
{
    public function assignRoleForUser(int $id, string $role):bool
    {
        $userId = $id;
        $user = User::find($userId);

        if (!$user) {
            return false;
        }
        
        $user->assignRole($role);
        return true;
    }

    public function revokeRoleForUser(int $id, string $role){
        $userId = $id;
        $user = User::find($userId);

        if (!$user) {
            return false;
        }
        $user->removeRole($role);
        return true;
    }
}
