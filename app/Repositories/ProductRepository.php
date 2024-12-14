<?php

namespace App\Repositories;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\Store;
use App\Models\Store_Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ProductRepository implements ProductRepositoryInterface
{
    public function findOrCreate($name, $categoryId)
    {
        $product = Product::where('name', $name)->where('category_id', $categoryId)->first();

        Log::info('in body of findOrCreate function in product repo');
        if (!$product) {
            return Product::create([
                'name' => $name,
                'category_id' => $categoryId,
            ]);
        }

        return $product;
    }

    public function get_all_product($itemsPerPage): LengthAwarePaginator
    {
        return Store_Product::with(['store:id,name', 'product:id,name,category_id', 'product.category:id,name'])
            ->paginate($itemsPerPage);
    }

    public function uploadImage(UploadedFile $file, string $directory, string $disk = 'public'): bool|string
    {
        return $file->store($directory, $disk);
    }

    public function findStoreProductById($storeId, $productId)
    {
        return Store_Product::select('store_id', 'product_id', 'price', 'quantity', 'description', 'main_image')
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->with([
                'store:id,name',
                'product:id,name,category_id',
                'product.category:id,name',
                'images' => function ($query) use ($storeId) {
                    $query->where('store_id', $storeId)
                    ->select('id', 'store_id', 'product_id', 'image');
                }
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

    public function incrementQuantity(Store_Product $storeProduct, $quantity)
    {
        $storeProduct->increment('quantity', $quantity);
    }

    public function updateProduct(Store_Product $storeProduct, array $data)
    {
        $storeProduct->update($data);
    }
}
