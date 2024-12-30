<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface OrderRepositoryInterface
{
    public function getStoreProductPrices($storeProductIds);

    public function createOrders(array $ordersData, array $orderProductsData);

    public function getUserOrders(User $user);

    public function findUserOrder(int $orderId, int $userId): ?object;

    public function getOrderProducts(int $orderId);

    public function deleteOrder(int $orderId): void;
}
