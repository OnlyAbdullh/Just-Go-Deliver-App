<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface FavoriteRepositoryInterface
{
    public function add(User $user,int $product_id, int $store_id);


}
