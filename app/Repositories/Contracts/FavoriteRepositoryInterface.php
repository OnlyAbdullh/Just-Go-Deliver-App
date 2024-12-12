<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface FavoriteRepositoryInterface
{
    public function add(User $user, int $product_id, int $store_id): void;
    public function remove(User $user, int $product_id, int $store_id): void;
    public function getAllFavorites(User $user): array;
   // public function isProductInStore(int $productId, int $storeId): bool;
    public function isFavorite(User $user, int $product_id, int $storeId): bool;

}
