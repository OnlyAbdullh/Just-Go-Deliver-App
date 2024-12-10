<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\UploadedFile;
use App\Models\Store_Product;

interface ProductRepositoryInterface
{
    public function findOrCreate($name, $categoryId);

    public function uploadImage(UploadedFile $file, string $directory, string $disk = 'public');

    public function findStoreProductById($storeId, $productId);

    public function incrementQuantity(Store_Product $storeProduct, $quantity);

    public function updateProduct(Store_Product $storeProduct, array $data);
}
