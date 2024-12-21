<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;

interface CartRepositoryInterface
{

    public function createCart(int $userId): Cart;

    public function getStoreProduct(int $storeId, int $productId);
    public function getCartProducts($cartId);
    public function addProductToCart(int $cartId, int $storeProductId, int $quantity): void;

    public function  UpdateCartProducts(array $updates): void;

    public function deleteCartProducts(int $cartId, array $storeProductIds): int;
}
