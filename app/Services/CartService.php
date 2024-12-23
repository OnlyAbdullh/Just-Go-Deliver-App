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

    public function getAllProductsInCart(User $user): array
    {
        $cart = $user->cart;

        if (! $cart) {
            return ['message' => __('messages.cart_empty')];
        }

        $products = $this->cartRepository->getCartProducts($cart);

        if ($products->isEmpty()) {
            return ['message' => __('messages.cart_empty')];
        }

        return ['data' => $products];
    }

    public function updateCartQuantities(int $cartId, array $items): array
    {
        $responses = [];
        $updates = [];

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

            $responses[] = $this->generateResponse($productId, $storeId, $requestedQuantity, $availableStock, $quantityToUpdate);
        }

        $this->cartRepository->UpdateCartProducts($updates);

        return $responses;
    }

    private function generateResponse(int $productId, int $storeId, int $requestedQuantity, int $availableStock, int $quantityToUpdate): array
    {
        $message = match (true) {
            $availableStock == 0 => __('messages.no_product_available', ['productId' => $productId]),
            $requestedQuantity > $availableStock => __('messages.only_available_stock', [
                'availableStock' => $availableStock,
                'productId' => $productId,
                'quantityToUpdate' => $quantityToUpdate,
            ]),
            default => __('messages.updated_product_quantity', [
                'productId' => $productId,
                'quantityToUpdate' => $quantityToUpdate,
            ]),
        };

        return [
            'message' => $message,
            'product_id' => $productId,
            'store_id' => $storeId,
            'cart_amount' => $quantityToUpdate,
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
}
