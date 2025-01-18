<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    public function getAllProductsForStore($items, $storeId);

    public function getProductStatistics($items, $storeId);

    public function getOrdersForStore($storeId);

    public function updateOrderStatus($orderId,$status);

    // public function getUserAndDeviceTokens($orderId);
}
