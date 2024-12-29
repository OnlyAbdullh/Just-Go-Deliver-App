<?php

namespace App\Repositories;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Order_Product;
use App\Models\Store_Product;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function getStoreProductPrices($storeProductIds)
    {
        return Store_Product::whereIn('id', $storeProductIds)
            ->lockForUpdate()
            ->pluck('price', 'id')
            ->toArray();
    }

    public function createOrders(array $ordersData, array $orderProductsData)
    {
        Order::insert($ordersData);
        $startingOrderId = Order::max('id') - count($ordersData) + 1;

        foreach ($orderProductsData as &$orderProduct) {
            $orderProduct['order_id'] = $startingOrderId + $orderProduct['order_reference_index'];
            unset($orderProduct['order_reference_index']);
        }

        Order_Product::insert($orderProductsData);

        event(new OrderCreated($orderProductsData));
        return ['order_count' => count($ordersData)];
    }
}
