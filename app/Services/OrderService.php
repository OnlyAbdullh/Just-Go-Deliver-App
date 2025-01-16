<?php

namespace App\Services;

use App\Events\OrderCancelled;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderService
{
    private $orderRepository;
    private $cartRepository;

    public function __construct(OrderRepositoryInterface $orderRepository, CartRepositoryInterface $cartRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
    }

    public function createOrders(array $data)
    {
        $user = Auth::user();
        $now = now();
        $result = $this->cartRepository->getCartProducts($user->cart, true);

        $requestedProductIds = collect($data)->pluck('store_product_id')->sort()->values();
        $cartProductIds = collect($result['products'])->pluck('store_product_id')->sort()->values();

        if ($requestedProductIds->diff($cartProductIds)->isNotEmpty() || $cartProductIds->diff($requestedProductIds)->isNotEmpty()) {
            return [
                'state' => false,
                'message' => 'Order Failed, Some Items in Your Cart Are Out of Stock'
            ];
        }
        return DB::transaction(function () use ($data, $user, $now) {
            $groupedProducts = collect($data)->groupBy('store_id');
            $storeProductIds = $groupedProducts->flatMap(fn($products) => $products->pluck('store_product_id'));

            $storeProductDetails = $this->orderRepository->getStoreProductPrices($storeProductIds);

            $ordersData = [];
            $orderProductsData = [];
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

              $this->orderRepository->createOrders($ordersData, $orderProductsData);
        });
    }

    private function generateOrderReference()
    {
        return 'ORD-' . now()->format('Ymd-His') . '-' . Str::random(6);
    }

    public function getUserOrders()
    {
        $user = Auth::user();
        $orders = $this->orderRepository->getUserOrders($user);

        return $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_date' => $order->order_date,
                'status' => $order->status,
                'total_price' => $order->total_price,
                'order_reference' => $order->order_reference,
                'number_of_products' => $order->number_of_products,
                'image' => $order->main_image ? asset(Storage::url($order->main_image)) : null,
            ];
        });
    }

    public function cancelOrder(int $orderId): array
    {
        $user = Auth::user();

        $order = $this->orderRepository->findUserOrder($orderId, $user->id);

        if (!$order) {
            return [
                'success' => false,
                'message' => __('messages.order_not_found'),
                'code' => 404,
            ];
        }

        if ($order->status !== 'pending') {
            return [
                'success' => false,
                'message' => __('messages.order_cannot_be_cancelled'),
            ];
        }

        DB::transaction(function () use ($order, $user) {
            $orderProducts = $this->orderRepository->getOrderProducts($order->id);

            $this->orderRepository->deleteOrder($order->id);

            event(new OrderCancelled($user, $order, $orderProducts));
        });

        return ['success' => true, 'message' => __('messages.order_cancelled')];
    }

    public function getOrderWithProducts(int $orderId)
    {
        return $this->orderRepository->findOrderWithProducts($orderId);
    }
}
