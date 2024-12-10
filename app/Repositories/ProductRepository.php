<?php

namespace App\Repositories;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\Store;
use App\Models\Store_Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ProductRepository implements ProductRepositoryInterface
{
    public function findOrCreate($name, $categoryId)
    {
        $product = Product::where('name', $name)->first();

        Log::info('in body of findOrCreate function in product repo');
        if (!$product) {
            return Product::create([
                'name' => $name,
                'category_id' => $categoryId,
            ]);
        }

        return $product;
    }

    public function uploadImage(UploadedFile $file, string $directory, string $disk = 'public'): bool|string
    {
        return $file->store($directory, $disk);
    }

    public function findStoreProductById($storeId, $productId)
    {

        return Store_Product::where('store_id', $storeId)
            ->where('product_id', $productId)
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
