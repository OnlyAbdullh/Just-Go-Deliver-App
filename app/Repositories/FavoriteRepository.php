<?php
namespace App\Repositories;

use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\FavoriteRepositoryInterface;

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

    public function getAllFavorites(User $user): array
    {
        return $user->favoriteProducts()
            ->with(['stores' => function ($query) {
                $query->select(
                    'stores.id',
                    'stores.name'
                )->withPivot('price', 'quantity', 'description', 'sold_quantity');
            }])
            ->get()
            ->flatMap(function ($product) {
                return $product->stores->map(function ($store) use ($product) {
                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'category_id' => $product->category_id,
                        'store_id' => $store->id,
                        'store_name' => $store->name,
                        'price' => $store->pivot->price,
                        'quantity' => $store->pivot->quantity,
                        'description' => $store->pivot->description,
                        'sold_quantity' => $store->pivot->sold_quantity,
                    ];
                });
            })
            ->unique(fn($item) => $item['product_id'] . '-' . $item['store_id'])
            ->toArray();
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
