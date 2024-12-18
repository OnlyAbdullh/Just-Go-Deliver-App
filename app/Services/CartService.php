<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class CartService
{
    protected $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function addProductToCart(int $store_id, int $product_id, int $quantity): array
    {
        $user = Auth::user();

        $cart = $user->cart ?? $this->cartRepository->createCart($user->id);

        $storeProduct = $this->cartRepository->getStoreProduct($store_id, $product_id);

        if ( $storeProduct->quantity < $quantity) {
            return ['success' => false, 'message' => 'Not enough stock available'];
        }

        $this->cartRepository->updateOrInsertCartProduct($cart->id, $storeProduct->id, $quantity);

        return ['success' => true, 'message' => 'Product added to cart successfully'];
    }
    public function getAllProductsInCart(User $user): array
    {
        // Fetch the cart for the user or create a new one if it doesn't exist
        $cart = $user->cart;

        if (!$cart) {
            return ['success' => false, 'message' => 'Your cart is empty'];
        }

        $products = $this->cartRepository->getCartProducts($cart->id);

        if ($products->isEmpty()) {
            return ['success' => false, 'message' => 'Your cart is empty'];
        }

        return ['success' => true, 'data' => $products];
    }
}
