<?php

namespace App\Services;

use App\Repositories\Contracts\DashboardRepositoryInterface;

class DashboardService
{
    private $dashboardRepository,$fcmService;

    public function __construct(DashboardRepositoryInterface $dashboardRepository,FcmService $fcmService)
    {
        $this->dashboardRepository = $dashboardRepository;
        $this->fcmService = $fcmService;
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
        $order =  $this->dashboardRepository->updateOrderStatus($orderId,$status);
        
        $tokens =  $this->dashboardRepository->getUserAndDeviceTokens($orderId);

        // if($tokens){
        //     $this->fcmService->sendNotification($tokens,__('messages.order_status'),__('messages.order_status_changed',['status' =>$status]));
        // }

        return $order;
    }
}
