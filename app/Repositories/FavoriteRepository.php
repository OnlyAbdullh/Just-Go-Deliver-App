<?php

namespace App\Repositories;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\FavoriteRepositoryInterface;
use Illuminate\Support\Facades\DB;

class FavoriteRepository implements FavoriteRepositoryInterface
{
    public function add(User $user, int $product_id, int $store_id): void
    {
        $user->favoriteProducts()->attach($product_id, [
            'store_id' => $store_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function remove(User $user, int $product_id, int $store_id): void
    {
        $user->favoriteProducts()->wherePivot('store_id', $store_id)->detach($product_id);
    }

    public function getAllFavorites(User $user)
    {
        $lang = app()->getLocale();
        $favorites = DB::table('favorites')
            ->join('products', 'favorites.product_id', '=', 'products.id')
            ->join('stores', 'favorites.store_id', '=', 'stores.id')
            ->join('store_products', function ($join) {
                $join->on('store_products.product_id', '=', 'products.id')
                    ->on('store_products.store_id', '=', 'favorites.store_id');
            })
            ->where('favorites.user_id', $user->id)
            ->select(
                'products.id as product_id',
                'products.name_' . $lang . ' as product_name',
                'products.category_id',
                'stores.id as store_id',
                'stores.name_' . $lang . ' as store_name',
                'store_products.price',
                'store_products.quantity',
                'store_products.description_' . $lang . ' as description',
                'store_products.main_image'
            )
            ->distinct()
            ->get();
        return $favorites;
    }

    /*    public function isProductInStore(int $productId, int $storeId): bool
        {
            $product = Product::find($productId);
            if (!$product) {
                return false;
            }
            return $product->stores()->where('stores.id', $storeId)->exists();
        }*/

    public function isFavorite(User $user, int $product_id, int $storeId): bool
    {
        return $user->favoriteProducts()
            ->where('product_id', $product_id)
            ->where('favorites.store_id', $storeId)
            ->exists();
    }
}
