<?php
namespace App\Repositories\Contracts;

use App\Models\Cart;

interface CartRepositoryInterface
{

    public function createCart(int $userId): Cart;

    public function getStoreProduct(int $storeId, int $productId);

    public function updateOrInsertCartProduct(int $cartId, int $storeProductId, int $quantity): void;
}
