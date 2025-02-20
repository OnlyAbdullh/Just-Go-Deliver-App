<?php

namespace App\Services;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\Contracts\StoreRepositoryInterface;

class DashboardService
{
    private $dashboardRepository;

    private $fcmService;

    private $storeRepository;

    public function __construct(DashboardRepositoryInterface $dashboardRepository, FcmService $fcmService, StoreRepositoryInterface $storeRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
        $this->fcmService = $fcmService;
        $this->storeRepository = $storeRepository;
    }

    public function getAllProductForStore($storeId)
    {
        return $this->dashboardRepository->getAllProductsForStore($storeId);
    }

    public function getProductStatistics($items, $storeId)
    {
        return $this->dashboardRepository->getProductStatistics($items, $storeId);
    }

    public function getAllOrdersForStore($storeId)
    {
        return $this->dashboardRepository->getOrdersForStore($storeId);
    }

    public function updateOrder($orderId, $status)
    {
        $order = $this->dashboardRepository->updateOrderStatus($orderId, $status);

        $tokens = $this->dashboardRepository->getUserAndDeviceTokens($orderId);

        $this->fcmService->sendNotification($tokens, __('messages.order_status'), __('messages.order_status_changed', ['status' => $status]));

        return $order;
    }
}
