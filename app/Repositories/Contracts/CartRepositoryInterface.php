<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;

interface CartRepositoryInterface
{
    public function createCart(int $userId): Cart;

    public function getStoreProduct(int $storeId, int $productId);

    public function getCartProducts(Cart $cart);

    public function addProductToCart(Cart $cart, int $storeProductId, int $quantity): void;

    public function UpdateCartProducts(array $updates): void;

    public function deleteCartProducts(Cart $cart, array $storeProductIds): int;
}
