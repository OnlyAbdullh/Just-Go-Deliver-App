<?php

namespace App\Repositories;


use App\Models\Cart;
use App\Models\CartProduct;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function addProductToCart(int $cartId, int $storeProductId, int $quantity): void
    {
        DB::table('cart_products')->insert([
            'cart_id' => $cartId,
            'store_product_id' => $storeProductId,
            'amount_needed' => $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function UpdateCartProducts(array $updates): void
    {
        foreach ($updates as $update) {
            DB::table('cart_products')
                ->where('cart_id', $update['cart_id'])
                ->where('store_product_id', $update['store_product_id'])
                ->update([
                    'amount_needed' => $update['amount_needed'],
                    'updated_at' => now(),
                ]);
        }
    }


    public function getCartProducts($cartId)
    {
        return CartProduct::query()
            ->where('cart_id', $cartId)
            ->with([
                'storeProduct:id,store_id,product_id,price,quantity,sold_quantity,description,main_image',
                'storeProduct.store:id,name',
                'storeProduct.product:id,name',
            ])
            ->get()
            ->map(function ($cartProduct) {
                $storeProduct = $cartProduct->storeProduct;
                $order_amount = $cartProduct->amount_needed;
                $availableStock = $storeProduct->quantity;
                if ($availableStock == 0) $message = 'No Products available for now.';
                else if ($availableStock < $order_amount) $message = 'only ' . $availableStock . ' is available.';
                else $message = 'available now';
                $mainUrl = Storage::url($storeProduct->main_image);
                return [
                    'store_id' => $storeProduct->store->id,
                    'store_name' => $storeProduct->store->name,
                    'order_quantity' => $cartProduct->amount_needed,
                    'store_product_id' => $storeProduct->id,
                    'price' => $storeProduct->price,
                    'quantity' => $storeProduct->quantity,
                    'description' => $storeProduct->description,
                    'product_id' => $storeProduct->product->id,
                    'product_name' => $storeProduct->product->name,
                    'main_image' => asset($mainUrl),
                    'message' => $message
                ];
            });

    }

    public function deleteCartProducts(int $cartId, array $storeProductIds): int
    {
        return DB::table('cart_products')
            ->where('cart_id', $cartId)
            ->whereIn('store_product_id', $storeProductIds)
            ->delete();
    }

}
