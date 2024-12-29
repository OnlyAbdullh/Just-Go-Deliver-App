<?php
namespace App\Repositories\Contracts;

interface OrderRepositoryInterface
{
    public function getStoreProductPrices($storeProductIds);
    public function createOrders(array $ordersData, array $orderProductsData);
}
