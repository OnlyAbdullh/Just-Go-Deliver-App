<?php

namespace App\Repositories;

use App\Events\NotifyQuantityAvailable;
use App\Models\Product;
use App\Models\Store;
use App\Models\Store_Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function findOrCreate($nameAr, $nameEn, $categoryId): Product
    {
        return Product::firstOrCreate(
            [
                'name_ar' => $nameAr,
                'name_en' => $nameEn,
                'category_id' => $categoryId,
            ]
        );
    }

    public function get_all_product($itemsPerPage): LengthAwarePaginator
    {
        $lang = app()->getLocale();

        return Store_Product::with([
            'store:id,name_'.$lang,
            'product:id,name_'.$lang.',category_id',
            'product.category:id,name_'.$lang,
        ])->select([
            'id',
            'store_id',
            'product_id',
            'description_'.$lang,
            'price',
            'quantity',
            'main_image',
            DB::raw('IF('.
                (auth()->check() ? 'EXISTS (SELECT 1 FROM favorites WHERE user_id = '.auth()->id().' AND product_id = store_products.product_id AND store_id = store_products.store_id)' : '0').
                ', 1, 0) AS is_favorite'),
        ])->paginate($itemsPerPage);
    }

    public function uploadImage(UploadedFile $file, string $directory, string $disk = 'public'): bool|string
    {
        return $file->store($directory, $disk);
    }

    public function findStoreProductById($storeId, $productId)
    {
        $lang = app()->getLocale();

        return Store_Product::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->with([
                'store:id,name_'.$lang,
                'product:id,name_'.$lang.',category_id',
                'product.category:id,name_'.$lang,
                'images' => function ($query) use ($storeId) {
                    $query->where('store_id', $storeId)
                        ->select('id', 'store_id', 'product_id', 'image');
                },
            ])->select([
                'id',
                'store_id',
                'product_id',
                'description_'.$lang,
                'price',
                'quantity',
                'main_image',
                DB::raw('IF('.
                    (auth()->check() ? 'EXISTS (SELECT 1 FROM favorites WHERE user_id = '.auth()->id().' AND product_id = store_products.product_id AND store_id = store_products.store_id)' : '0').
                    ', 1, 0) AS is_favorite'),

                DB::raw('(SELECT SUM(cart_products.amount_needed)
                  FROM cart_products
                  JOIN carts ON carts.id = cart_products.cart_id
                  WHERE cart_products.store_product_id = store_products.id
                  AND carts.user_id = '.(auth()->check() ? auth()->id() : 'NULL').') AS total_amount_needed'),
            ])
            ->first();
    }

    public function findProductInStore(Store $store, $productId)
    {
        return $store->products()
            ->wherePivot('product_id', $productId)
            ->with('images')
            ->first();
    }

    public function incrementQuantity($store, $productId, $storeProduct, $quantity)
    {
        event(new NotifyQuantityAvailable($storeProduct->id));
        $store->products()->updateExistingPivot($productId, [
            'quantity' => $storeProduct->pivot->quantity + $quantity,
        ]);
    }

    public function updateProduct(Store_Product $storeProduct, array $data)
    {
        $storeProduct->update($data);
    }

    public function findByName($items, $name)
    {
        $lang = app()->getLocale();

        return Store_Product::query()
            ->whereHas('product', function ($query) use ($name, $lang) {
                $query->where('name_'.$lang, 'like', '%'.$name.'%');
            })
            ->with([
                'store:id,name_'.$lang,
                'product:id,name_'.$lang.',category_id',
                'product.category:id,name_'.$lang,
            ])
            ->select([
                'store_id',
                'product_id',
                'description_'.$lang,
                'price',
                'quantity',
                'main_image',
                DB::raw('IF('.
                    (auth()->check() ? 'EXISTS (SELECT 1 FROM favorites WHERE user_id = '.auth()->id().' AND product_id = store_products.product_id AND store_id = store_products.store_id)' : '0').
                    ', 1, 0) AS is_favorite'),
            ])
            ->paginate($items);
    }
}
