<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    public function getAllProductsForStore($items, $storeId);

    public function getProductStatistics($items,$storeId);
}
