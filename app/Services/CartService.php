<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class CartService
{
    protected $cartRepository;

    protected $productRepository;

    public function __construct(CartRepositoryInterface $cartRepository, ProductRepositoryInterface $productRepository)
    {
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
    }

    public function addProductToCart(int $store_id, int $product_id, int $quantity): array
    {
        $user = Auth::user();

        $cart = $user->cart ?? $this->cartRepository->createCart($user->id);

        $storeProduct = $this->cartRepository->getStoreProduct($store_id, $product_id);

        if ($storeProduct->quantity < $quantity) {
            return ['success' => false, 'message' => __('messages.not_enough_stock')];
        }

        $this->cartRepository->addProductToCart($cart, $storeProduct->id, $quantity);

        return ['success' => true, 'message' => __('messages.product_added_to_cart')];
    }

    public function addProductsToCartAgain(Cart $cart, array $cartProducts): array
    {
        $this->cartRepository->addProductsToCartBatch($cart, $cartProducts);

        return ['success' => true, 'message' => __('messages.product_added_to_cart')];
    }

    public function getAllProductsInCart(User $user): array
    {
        $cart = $user->cart;

        if (!$cart) {
            return ['message' => __('messages.cart_empty')];
        }

        $cartData = $this->cartRepository->getCartProducts($cart);

        if (empty($cartData['products'])) {
            return ['message' => __('messages.cart_empty')];
        }

        return $cartData;
    }

    public function updateCartQuantities(Cart $cart, array $items)
    {
        $updates = [];
        $cartId = $cart->id;
        $updatedProductIds = [];
        foreach ($items as $item) {
            $storeId = $item['store_id'];
            $productId = $item['product_id'];
            $requestedQuantity = $item['quantity'];

            $storeProduct = $this->productRepository->findStoreProductById($storeId, $productId);

            $availableStock = $storeProduct->quantity;
            $quantityToUpdate = min($requestedQuantity, $availableStock);
            if ($quantityToUpdate == 0) {
                $quantityToUpdate = $requestedQuantity;
            }
            $updates[] = [
                'cart_id' => $cartId,
                'store_product_id' => $storeProduct->id,
                'amount_needed' => $quantityToUpdate,
            ];

            $updatedProductIds[] = $productId;
        }

        $this->cartRepository->UpdateCartProducts($updates);

        $cartProducts = $this->cartRepository->getCartProducts($cart);
        $products = $cartProducts['products'];
        $response = $products->map(function ($product) use ($updatedProductIds, $requestedQuantity) {

            if (in_array($product['product_id'], $updatedProductIds)) {

                $generatedResponse = $this->generateResponse(
                    $requestedQuantity,
                    $product['quantity'],
                );

                $product['message'] = $generatedResponse['message'];
            }

            return $product;
        });
        $cart->refresh();
        return ['products' => $response, 'total_price' => $cart->total_price];
    }

    private function generateResponse(int $requestedQuantity, int $availableStock): array
    {
        $message = match (true) {
            $availableStock == 0 => __('messages.no_product_available'),
            $requestedQuantity > $availableStock => __('messages.only_available_stock', [
                'availableStock' => $availableStock,
                'requestedQuantity' => $requestedQuantity,
            ]),
            default => __('messages.available_now'),
        };

        return [
            'message' => $message,
        ];
    }

    public function DeleteCartProducts(Cart $cart, array $items): int
    {
        $ids = [];
        foreach ($items as $item) {
            $productId = $item['product_id'];
            $storeId = $item['store_id'];
            $storeProduct = $this->productRepository->findStoreProductById($storeId, $productId);
            $ids[] = $storeProduct->id;
        }

        return $this->cartRepository->deleteCartProducts($cart, $ids);
    }

    public function deleteAll()
    {
        $user = Auth::user();
        $this->cartRepository->deleteAll($user);
    }
}
