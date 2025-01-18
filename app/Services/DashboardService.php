<?php

namespace App\Services;

use App\Repositories\Contracts\DashboardRepositoryInterface;

class DashboardService
{
    private $dashboardRepository;

    public function __construct(DashboardRepositoryInterface $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function getAllProductForStore($items, $storeId)
    {
        return $this->dashboardRepository->getAllProductsForStore($items, $storeId);
    }

    public function getProductStatistics($items, $storeId)
    {
        return $this->dashboardRepository->getProductStatistics($items, $storeId);
    }

    public function getAllOrdersForStore($storeId){
        return $this->dashboardRepository->getOrdersForStore($storeId);
    }

    public function  updateOrder($orderId,$status){
        return $this->dashboardRepository->updateOrderStatus($orderId,$status);
    }
}
