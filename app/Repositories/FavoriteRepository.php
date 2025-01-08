<?php

namespace App\Repositories;

use App\Http\Resources\FavoriteResource;
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
        $favorites = DB::table('favorites')
            ->join('products', 'favorites.product_id', '=', 'products.id')
            ->join('stores', 'favorites.store_id', '=', 'stores.id')
            ->join('store_products', function ($join) {
                $join->on('store_products.product_id', '=', 'products.id')
                    ->on('store_products.store_id', '=', 'favorites.store_id');
            })
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('favorites.user_id', $user->id)
            ->select(
                'favorites.store_id',
                'stores.name_ar as store_name_ar',
                'stores.name_en as store_name_en',
                'favorites.product_id',
                'products.name_ar as product_name_ar',
                'products.name_en as product_name_en',
                'products.category_id',
                'categories.name_ar as category_name_ar',
                'categories.name_en as category_name_en',
                'store_products.price',
                'store_products.quantity',
                'store_products.description_ar',
                'store_products.description_en',
                'store_products.main_image'
            )
            ->distinct()
            ->get();

        return FavoriteResource::collection($favorites);
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
