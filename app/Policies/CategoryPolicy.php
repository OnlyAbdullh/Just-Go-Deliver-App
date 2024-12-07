<?php

namespace App\Policies;

use App\Models\User;

class CategoryPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function createCategory(User $user)
    {
        $user->hasRole('store_admin');
    }
    
    public function updateCategory(User $user)
    {
        $user->hasRole('store_admin');
    }
    
    public function deleteCategory(User $user)
    {
        $user->hasRole('store_admin');
    }
}
