<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;

interface CartRepositoryInterface
{

    public function createCart(int $userId): Cart;

    public function getStoreProduct(int $storeId, int $productId);

    public function addProductToCart(int $cartId, int $storeProductId, int $quantity): void;

    public function updateCartProduct(int $cartId, int $storeProductId, int $quantity): void;
}
