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
    public function updateCartProduct(int $cartId, int $storeProductId, int $quantity): void
    {
        DB::table('cart_products')
            ->where('cart_id', $cartId)
            ->where('store_product_id', $storeProductId)
            ->update([
                'amount_needed' => $quantity,
                'updated_at' => now(),
            ]);
    }



    public function getCartProducts($cartId)
    {
        $cartProducts = CartProduct::query()
            ->where('cart_id', $cartId)
            ->with([
                'storeProduct:id,store_id,product_id,price,quantity,sold_quantity,description,main_image',
                'storeProduct.store:id,name',
                'storeProduct.product:id,name',
            ])
            ->get()
            ->map(function ($cartProduct) {
                $storeProduct = $cartProduct->storeProduct;
                $mainUrl = Storage::url($storeProduct->main_image);
                return [
                    'store_id'        => $storeProduct->store->id,
                    'store_name'      => $storeProduct->store->name,
                    'order_quantity'  => $cartProduct->amount_needed,
                    'store_product_id' => $storeProduct->id,
                    'price'           => $storeProduct->price,
                    'quantity'        => $storeProduct->quantity,
                    'description'     => $storeProduct->description,
                    'product_id'      => $storeProduct->product->id,
                    'product_name'    => $storeProduct->product->name,
                    'main_image'        => asset($mainUrl),
                ];
            });

        return $cartProducts;
    }

}
