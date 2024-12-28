<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\Order;
use App\Models\Order_Product;
use App\Models\Store_Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function createOrders(Request $request)
    {
        $data = $request->input('data');
        $user = Auth::user();

        $groupedProducts = collect($data)->groupBy('store_id');
        $storeProductIds = $groupedProducts->flatMap(fn($products) => $products->pluck('store_product_id'));
        $storeProductDetails = Store_Product::whereIn('id', $storeProductIds)->get()->keyBy('id');

        $ordersData = [];
        $orderProductsData = [];
        $now = now();

        foreach ($groupedProducts as  $storeProducts) {
            $totalPrice = $storeProducts->reduce(function ($total, $product) use ($storeProductDetails) {
                $storeProduct = $storeProductDetails->get($product['store_product_id']);
                return $total + ($storeProduct->price * $product['quantity']);
            }, 0);

            $orderReference = 'ORD-' . $now->format('Ymd-His') . '-' . Str::random(6);
            $ordersData[] = [
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'order_date' => $now,
                'order_reference' => $orderReference,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach ($storeProducts as $product) {
                $storeProduct = $storeProductDetails->get($product['store_product_id']);
                $orderProductsData[] = [
                    'store_product_id' => $storeProduct->id,
                    'quantity' => $product['quantity'],
                    'price' => $storeProduct->price,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        Order::insert($ordersData);

        $lastInsertedOrderId = Order::max('id') - count($ordersData) + 1;
        foreach ($orderProductsData as $index => $orderProduct) {
            $orderProductsData[$index]['order_id'] = $lastInsertedOrderId + floor($index / $groupedProducts->count());
        }

        Order_Product::insert($orderProductsData);

        return JsonResponseHelper::successResponse('Orders created successfully.');
    }

}
