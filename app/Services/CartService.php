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

        foreach ($items as $item) {
            $storeId = $item['store_id'];
            $productId = $item['product_id'];
            $requestedQuantity = $item['quantity'];

            $storeProduct = $this->productRepository->findStoreProductById($storeId, $productId);

            $availableStock = $storeProduct->quantity;

            if ($availableStock == 0) {
                $responses[] = [
                    'message' => "There is no product available for now for Product ID {$productId}.",
                    'product_id' => $productId,
                    'store_id' => $storeId,
                    'cart_amount' => $requestedQuantity,
                ];
                continue;
            }

            if ($requestedQuantity > $availableStock) {
                $this->cartRepository->updateCartProduct($cartId, $storeProduct->id, $availableStock);
                $responses[] = [
                    'message' => "Only {$availableStock} of Product ID {$productId} is available. Updated the quantity to {$availableStock}.",
                    'product_id' => $productId,
                    'store_id' => $storeId,
                    'cart_amount' => $availableStock,
                ];
                continue;
            }

            $this->cartRepository->updateCartProduct($cartId, $storeProduct->id, $requestedQuantity);
            $responses[] = [
                'message' => "Updated Product ID {$productId} to quantity {$requestedQuantity}.",
                'product_id' => $productId,
                'store_id' => $storeId,
                'cart_amount' => $requestedQuantity,
            ];
        }

        return $responses;
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
        return $this->cartRepository->deleteCartProducts($cartId,$ids);
    }
}
