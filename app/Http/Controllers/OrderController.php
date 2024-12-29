<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\Order;
use App\Models\Order_Product;
use App\Models\Store_Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function createOrders(Request $request)
    {
        $data = $request->input('data');
        $user = Auth::user();

        $groupedProducts = collect($data)->groupBy('store_id');
        $storeProductIds = $groupedProducts->flatMap(fn($products) => $products->pluck('store_product_id'))->unique();
        $storeProductDetails = Store_Product::whereIn('id', $storeProductIds)->pluck('price', 'id');

        $ordersData = [];
        $orderProductsData = [];
        $now = now();

        $orderCounter = 0;
        foreach ($groupedProducts as  $storeProducts) {
            $totalPrice = $storeProducts->sum(function ($product) use ($storeProductDetails) {
                return $storeProductDetails[$product['store_product_id']] * $product['quantity'];
            });

            $ordersData[$orderCounter] = [
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'order_date' => $now,
                'order_reference' => $this->generateOrderReference(),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach ($storeProducts as $product) {
                $orderProductsData[] = [
                    'store_product_id' => $product['store_product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $storeProductDetails[$product['store_product_id']],
                    'order_reference_index' => $orderCounter,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $orderCounter++;
        }

        Order::insert($ordersData);
        $startingOrderId = Order::max('id') - count($ordersData) + 1;

        foreach ($orderProductsData as &$orderProduct) {
            $orderProduct['order_id'] = $startingOrderId + $orderProduct['order_reference_index'];
            unset($orderProduct['order_reference_index']);
        }

        Order_Product::insert($orderProductsData);

        return JsonResponseHelper::successResponse('Orders created successfully.');
    }
    private function generateOrderReference()
    {
        return 'ORD-' . now()->format('Ymd-His') . '-' . Str::random(6);
    }

}
