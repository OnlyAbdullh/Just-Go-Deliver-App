<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function add(Store $store, Product $product, Request $request)
    {

        $user = Auth::user();


        $cart = $user->cart ?? Cart::create([
            'user_id' => $user->id,
        ]);

        $storeProduct = DB::table('store_products')
            ->where('store_id', $store->id)
            ->where('product_id', $product->id)
            ->first();

        if ($storeProduct->quantity < $request->quantity) {
            return response()->json(['message' => 'Not enough stock available'], 400);
        }

        DB::table('cart_products')->updateOrInsert(
            [
                'cart_id' => $cart->id,
                'store_product_id' => $storeProduct->id,
            ],
            [
                'quantity' => $request->quantity,
                'updated_at' => now(),
            ]
        );

        return response()->json(['message' => 'Product added to cart successfully']);
    }

}
