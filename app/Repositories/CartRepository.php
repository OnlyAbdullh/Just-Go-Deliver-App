<?php
namespace App\Repositories;


use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CartRepository implements CartRepositoryInterface
{
    public function createCart(int $userId): Cart
    {
        return Cart::create(['user_id' => $userId]);
    }

    public function getStoreProduct(int $storeId, int $productId)
    {
        return DB::table('store_products')
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->first();
    }

    public function updateOrInsertCartProduct(int $cartId, int $storeProductId, int $quantity): void
    {
        DB::table('cart_products')->updateOrInsert(
            [
                'cart_id' => $cartId,
                'store_product_id' => $storeProductId,
            ],
            [
                'amount_needed' => $quantity,
                'updated_at' => now(),
            ]
        );
    }

}
