<?php

namespace App\Services;

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
            return ['success' => false, 'message' => 'Not enough stock available'];
        }

        $this->cartRepository->addProductToCart($cart->id, $storeProduct->id, $quantity);

        return ['success' => true, 'message' => 'Product added to cart successfully'];
    }

    public function getAllProductsInCart(User $user): array
    {
        $cart = $user->cart;

        if (!$cart) {
            return ['message' => 'Your cart is empty'];
        }

        $products = $this->cartRepository->getCartProducts($cart->id);

        if ($products->isEmpty()) {
            return ['message' => 'Your cart is empty'];
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
            $availableStock == 0 => "There is no product available for now for Product ID {$productId}.",
            $requestedQuantity > $availableStock => "Only {$availableStock} of Product ID {$productId} is available. Updated the quantity to {$quantityToUpdate}.",
            default => "Updated Product ID {$productId} to quantity {$quantityToUpdate}.",
        };

        return [
            'message' => $message,
            'product_id' => $productId,
            'store_id' => $storeId,
            'cart_amount' => $quantityToUpdate,
        ];
    }


    public function DeleteCartProducts(int $cartId, array $items): int
    {
        $ids = [];
        foreach ($items as $item) {
            $productId = $item['product_id'];
            $storeId = $item['store_id'];
            $storeProduct = $this->productRepository->findStoreProductById($storeId, $productId);
            $ids[] = $storeProduct->id;
        }
        return $this->cartRepository->deleteCartProducts($cartId, $ids);
    }
}
