<?php
namespace App\Services;

use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderService
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function createOrders(array $data)
    {
        $user = Auth::user();

        $groupedProducts = collect($data)->groupBy('store_id');
        $storeProductIds = $groupedProducts->flatMap(fn($products) => $products->pluck('store_product_id'));

        $storeProductDetails = $this->orderRepository->getStoreProductPrices($storeProductIds);

        $ordersData = [];
        $orderProductsData = [];
        $now = now();

        $orderCounter = 0;
        foreach ($groupedProducts as $storeProducts) {
            $totalPrice = $storeProducts->sum(fn($product) => $storeProductDetails[$product['store_product_id']] * $product['quantity']);

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

        return $this->orderRepository->createOrders($ordersData, $orderProductsData);
    }

    private function generateOrderReference()
    {
        return 'ORD-' . now()->format('Ymd-His') . '-' . Str::random(6);
    }
}
