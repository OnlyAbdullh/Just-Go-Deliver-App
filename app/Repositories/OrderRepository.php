<?php

namespace App\Repositories;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Order_Product;
use App\Models\Store_Product;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

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

    public function getUserOrders(User $user)
    {
        return DB::table('orders')
            ->join('order_products', 'orders.id', '=', 'order_products.order_id')
            ->join('store_products', 'order_products.store_product_id', '=', 'store_products.id')
            ->select(
                'orders.id',
                'orders.order_date',
                'orders.status',
                'orders.total_price',
                'orders.order_reference',
                DB::raw('COUNT(order_products.id) as number_of_products'),
                DB::raw('MAX(store_products.main_image) as main_image')
            )
            ->where('orders.user_id', $user->id)
            ->groupBy('orders.id', 'orders.order_date', 'orders.status', 'orders.total_price', 'orders.order_reference')
            ->orderBy('orders.order_date', 'desc')
            ->get();
    }

    public function findUserOrder(int $orderId, int $userId): ?object
    {
        return DB::table('orders')
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();
    }

    public function getOrderProducts(int $orderId)
    {
        return DB::table('order_products')
            ->where('order_id', $orderId)
            ->get();
    }

    public function deleteOrder(int $orderId): void
    {
        DB::table('orders')
            ->where('id', $orderId)
            ->delete();
    }
    public function findOrderWithProducts(int $orderId)
    {
        $lang = app()->getLocale();

        return Order::with([
            'orderProducts.storeProduct.product.category',
            'orderProducts.storeProduct.store',
        ])
            ->join('order_products', 'orders.id', '=', 'order_products.order_id')
            ->join('store_products', 'order_products.store_product_id', '=', 'store_products.id')
            ->select([
                'orders.*',
                'order_products.store_product_id',
                'store_products.store_id',
                'store_products.product_id',
                'store_products.description_' . $lang,
                'store_products.price',
                'store_products.quantity',
                'store_products.main_image',
                DB::raw('IF(' .
                    (auth()->check() ? 'EXISTS (SELECT 1 FROM favorites WHERE user_id = ' . auth()->id() . ' AND product_id = store_products.product_id AND store_id = store_products.store_id AND user_id = orders.user_id)' : '0') .
                    ', 1, 0) AS is_favorite')
            ])
            ->where('orders.id', $orderId)
            ->first();
    }
}
