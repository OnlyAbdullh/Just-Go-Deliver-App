<?php

namespace App\Repositories;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getAllProductsForStore($items, $storeId)
    {
        $lang = app()->getLocale();

        return DB::table('store_products')
            ->where('store_products.store_id', $storeId)
            ->join('products', 'store_products.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select([
                'store_products.id as store_product_id',
                'store_products.product_id',
                'products.name_'.$lang.' as product_name',
                'products.category_id',
                'categories.name_'.$lang.' as category_name',
                'store_products.main_image',
                'store_products.price',
                'store_products.quantity',
                'store_products.description_'.$lang.' as description',
            ])
            ->paginate($items);
    }

    public function getProductStatistics($items, $storeId)
    {
        $lang = app()->getLocale();

        $products = DB::table('store_products')
            ->where('store_products.store_id', $storeId)
            ->join('products', 'store_products.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_products', 'store_products.id', '=', 'order_products.store_product_id')
            ->leftJoin('orders', 'order_products.order_id', '=', 'orders.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->select([
                'store_products.id as store_product_id',
                'products.id as product_id',
                'products.name_'.$lang.' as product_name',
                DB::raw('CONCAT("'.asset('storage/').'/", store_products.main_image) as main_image'),
                'store_products.price',
                'store_products.quantity',
                'store_products.description_'.$lang.' as description',
                'products.category_id',
                'categories.name_'.$lang.' as category_name',
                DB::raw('COALESCE(CAST(SUM(order_products.quantity) AS INTEGER), 0) as total_quantity_sold'),
                DB::raw('GROUP_CONCAT(CONCAT(users.first_name, " ", users.last_name) SEPARATOR ", ") as users_who_bought'),
            ])
            ->groupBy(
                'store_products.id',
                'store_products.main_image',
                'store_products.price',
                'store_products.quantity',
                'store_products.sold_quantity',
                'store_products.description_'.$lang,
                'products.id',
                'products.name_'.$lang,
                'products.category_id',
                'categories.name_'.$lang
            )
            ->orderBy('store_products.id')
            ->paginate($items);

        $products->transform(function ($product) {
            // Convert the users_who_bought to an array
            $product->users_who_bought = $product->users_who_bought ? explode(', ', $product->users_who_bought) : [];

            return $product;
        });

        return $products;
    }

    public function getOrdersForStore($storeId){
        $orders = $orders = DB::table('orders')
        ->join('order_products', 'orders.id', '=', 'order_products.order_id')
        ->join('store_products', 'order_products.store_product_id', '=', 'store_products.id')
        ->join('users', 'orders.user_id', '=', 'users.id')
        ->where('store_products.store_id', $storeId)
        ->orderBy('orders.order_date', 'asc')
        ->select(
            'orders.id as order_id',
            'orders.status',
            'orders.order_date',
            'orders.total_price',
            DB::raw('CONCAT(users.first_name, " ", users.last_name) as owner_order'),
            DB::raw('COUNT(order_products.id) as number_of_products')
        )
        ->groupBy('orders.id', 'orders.status', 'orders.order_date', 'orders.total_price', 'users.first_name', 'users.last_name')
        ->get()
        ->map(function ($order) {
            return [
                'id' => $order->order_id,
                'status' => $order->status,
                'order_date' => $order->order_date,
                'owner_order' => $order->owner_order,
                'total_price' => $order->total_price,
                'number_of_products' => $order->number_of_products,
            ];
        });

        return $orders;
    }

    public function updateOrderStatus($orderId,$status){
        return DB::table('orders')
            ->where('orders.id', $orderId)
            ->select('orders.id', 'users.id')
            ->update([
                'status' => $status,
                'updated_at' => Carbon::now(),
            ]);
    }

    public function getUserAndDeviceTokens($orderId){
        
        $userDevices = DB::table('orders') 
        ->join('device_tokens','orders.user_id', '=','device_tokens.user_id')
        ->where('orders.id',$orderId)
        ->select(['orders.user_id','device_tokens.fcm_token'])
        ->get();
        
        if($userDevices->isNotEmpty()){
            $userTokens = $userDevices->pluck('fcm_token')->toArray();
            return $userTokens;
        }
        return null;
    }
}
