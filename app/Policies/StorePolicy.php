<?php

namespace App\Policies;

use App\Models\Store;
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

    public function createStore(User $user)
    {
        return $user->hasRole('store_admin');
    }

    public function updateStore(User $user)
    {
        return $user->hasRole('store_admin');
    }

    public function deleteStore(User $user)
    {
        return $user->hasRole('store_admin');
    }

    public function addProductToStore(User $user, Store $store)
    {
        return $user->hasRole('store_admin') && $store->user_id === $user->id;
    }

    public function updateProduct(User $user, Store $store)
    {
        return $user->hasRole('store_admin') && $store->user_id === $user->id;
    }
}
