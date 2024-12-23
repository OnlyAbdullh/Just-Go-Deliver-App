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

    public function addProductToCart(Cart $cart, int $storeProductId, int $quantity): void
    {
        $cart->increment('cart_count');
        DB::table('cart_products')->insert([
            'cart_id' => $cart->id,
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


    public function getCartProducts(Cart $cart)
    {
        $lang = app()->getLocale();
        return CartProduct::query()
            ->where('cart_id', $cart->id)
            ->with([
                'storeProduct:id,store_id,product_id,price,quantity,sold_quantity,description_' . $lang . ',main_image',
                'storeProduct.store:id,name_' . $lang,
                'storeProduct.product:id,category_id,name_' . $lang,
                'storeProduct.product.favoritedByUsers' => function ($query) {
                    $query->where('user_id', auth()->id());
                },
                'storeProduct.product.category:id,name_' . $lang,
            ])
            ->get()
            ->map(function ($cartProduct) use ($lang) {
                $storeProduct = $cartProduct->storeProduct;
                $isFavorite = $storeProduct->product->favoritedByUsers->isNotEmpty() ? 1 : 0;
                $order_amount = $cartProduct->amount_needed;
                $availableStock = $storeProduct->quantity;
                if ($availableStock == 0) $message = __('messages.no_stock_available');
                else if ($availableStock < $order_amount) $message = __('messages.only_available', ['quantity' => $availableStock]);
                else $message = __('messages.available_now');

                $description = $storeProduct->{'description_' . $lang};
                $productName = $storeProduct->product->{'name_' . $lang};
                $storeName = $storeProduct->store->{'name_' . $lang};
                $categoryName = $storeProduct->product->category->{'name_' . $lang};

                $mainUrl = Storage::url($storeProduct->main_image);

                return [
                    'store_id' => $storeProduct->store->id,
                    'store_name' => $storeName,
                    'order_quantity' => $cartProduct->amount_needed,
                    'store_product_id' => $storeProduct->id,
                    'price' => $storeProduct->price,
                    'quantity' => $storeProduct->quantity,
                    'description' => $description,
                    'product_id' => $storeProduct->product->id,
                    'product_name' => $productName,
                    'category_id' => $storeProduct->product->category_id,
                    'category_name' => $categoryName,
                    'main_image' => asset($mainUrl),
                    'is_favorite' => $isFavorite,
                    'message' => $message
                ];
            });
    }

    public function deleteCartProducts(Cart $cart, array $storeProductIds): int
    {
        $productCount = count($storeProductIds);

        $cart->decrement('cart_count', $productCount);

        return DB::table('cart_products')
            ->where('cart_id', $cart->id)
            ->whereIn('store_product_id', $storeProductIds)
            ->delete();
    }
}
