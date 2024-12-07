<?php

namespace App\Repositories;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\Store;
use App\Models\Store_Product;
use Illuminate\Http\UploadedFile;

class ProductRepository implements ProductRepositoryInterface
{
    public function findOrCreate($data)
    {
        return Product::firstOrCreate(
            ['name' => $data['name']],
            ['main_image' => $data['main_image']]
        );
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
