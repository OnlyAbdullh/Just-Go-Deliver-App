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
        $orders = DB::table('orders')
        ->join('order_products', 'orders.id', '=', 'order_products.order_id')
        ->join('store_products', 'order_products.store_product_id', '=', 'store_products.id')
        ->where('store_products.store_id', $storeId)
        ->orderBy('orders.order_date', 'asc')
        ->select(
            'orders.id as order_id',
            'orders.order_reference',
            'orders.user_id',
            'orders.total_price',
            'orders.status',
            'orders.order_date',
            'orders.created_at as order_created_at',
            'orders.updated_at as order_updated_at',
            'order_products.id as order_product_id',
            'order_products.quantity as order_product_quantity',
            'order_products.price as order_product_price',
            'order_products.created_at as order_product_created_at',
            'order_products.updated_at as order_product_updated_at',
            'store_products.id as store_product_id',
            'store_products.store_id',
            'store_products.product_id',
            'store_products.main_image',
            'store_products.price as store_product_price',
            'store_products.quantity as store_product_quantity',
            'store_products.description_en',
            'store_products.description_ar',
            'store_products.sold_quantity',
            'store_products.created_at as store_product_created_at',
            'store_products.updated_at as store_product_updated_at'
        )
        ->get()
        ->groupBy('order_id') 
        ->map(function ($orderGroup) {
            $firstOrder = $orderGroup->first();
    
            return [
                'id' => $firstOrder->order_id,
                'order_reference' => $firstOrder->order_reference,
                'user_id' => $firstOrder->user_id,
                'total_price' => $firstOrder->total_price,
                'status' => $firstOrder->status,
                'order_date' => $firstOrder->order_date,
                'created_at' => $firstOrder->order_created_at,
                'updated_at' => $firstOrder->order_updated_at,
                'order_products' => $orderGroup->map(function ($item) {
                    return [
                        'id' => $item->order_product_id,
                        'order_id' => $item->order_id,
                        'store_product_id' => $item->store_product_id,
                        'quantity' => $item->order_product_quantity,
                        'price' => $item->order_product_price,
                        'created_at' => $item->order_product_created_at,
                        'updated_at' => $item->order_product_updated_at,
                        'store_product' => [
                            'id' => $item->store_product_id,
                            'store_id' => $item->store_id,
                            'product_id' => $item->product_id,
                            'main_image' => $item->main_image,
                            'price' => $item->store_product_price,
                            'quantity' => $item->store_product_quantity,
                            'description_en' => $item->description_en,
                            'description_ar' => $item->description_ar,
                            'sold_quantity' => $item->sold_quantity,
                            'created_at' => $item->store_product_created_at,
                            'updated_at' => $item->store_product_updated_at,
                        ],
                    ];
                })->values(), // Reset keys for order_products array
            ];
        })
        ->values();

        return $orders;
    }

    public function updateOrderStatus($orderId,$status){
        return DB::table('orders')
            ->where('orders.id', $orderId)
            // ->where('orders.user_id', $validated['seller_id'])
            ->select('orders.id', 'users.id')
            ->update([
                'status' => $status,
                'updated_at' => Carbon::now(),
            ]);
    }
}
