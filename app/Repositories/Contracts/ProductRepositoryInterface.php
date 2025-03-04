<?php

namespace App\Repositories\Contracts;

use App\Models\Store;
use App\Models\Store_Product;
use Illuminate\Http\UploadedFile;

interface ProductRepositoryInterface
{
    public function get_all_product($itemsPerPage);

    public function findOrCreate($nameAr, $nameEn, $categoryId);

    public function uploadImage(UploadedFile $file, string $directory, string $disk = 'public');

    public function findStoreProductById($storeId, $productId);

    public function findProductInStore(Store $store, $productId);

    public function incrementQuantity($store, $productId, $storeProduct, $quantity);

    public function updateProduct(Store_Product $storeProduct, array $data);

    public function findByName($items, $name);
}
