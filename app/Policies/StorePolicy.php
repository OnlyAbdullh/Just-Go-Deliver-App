<?php

namespace App\Policies;

use App\Models\User;

class StorePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function createStore(User $user){
        return $user->hasRole('store_admin');
    }

    public function updateStore(User $user){
        return $user->hasRole('store_admin');
    }

    public function deleteStore(User $user){
        return $user->hasRole('store_admin');
    }
}
