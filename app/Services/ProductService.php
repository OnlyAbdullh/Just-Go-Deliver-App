<?php

namespace App\Services;

use App\Helpers\JsonResponseHelper;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Store;
use App\Models\Product;
use App\Models\Store_Product;
use App\Repositories\Contracts\ImageRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\StoreRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    private $productRepository, $storeRepository, $categoryService, $imageRepository;

    public function __construct(ProductRepositoryInterface $productRepository, ImageRepositoryInterface $imageRepository, StoreRepositoryInterface $storeRepository, CategoryService $categoryService)
    {
        $this->productRepository = $productRepository;
        $this->storeRepository = $storeRepository;
        $this->imageRepository = $imageRepository;
        $this->categoryService = $categoryService;
    }

    public function getAllProduct($items)
    {
        return $this->productRepository->get_all_product($items);
    }

    public function showProduct(Store $store, Product $product)
    {
        return $this->productRepository->findStoreProductById($store->id, $product->id);
    }

    public function addProductToStore($data, Store $store): bool|null
    {
        $imagePath = $this->productRepository->uploadImage($data['main_image'], 'products');

        $category = $this->categoryService->findOrCreate($data['category_name_en'], $data['category_name_ar']);

        $product = $this->productRepository->findOrCreate($data['name_ar'], $data['name_en'], $category->id);

        $storeProduct = $this->productRepository->findProductInStore($store, $product->id);

        if ($storeProduct) {
            $this->productRepository->incrementQuantity($store, $product->id, $storeProduct, $data['quantity']);
            return true;
        }

        $this->imageRepository->store($store->id, $product->id, $data['sub_images']);

        $store->products()->attach($product->id, [
            'price' => $data['price'],
            'quantity' => $data['quantity'],
            'description_ar' => $data['description_ar'],
            'description_en' => $data['description_en'],
            'main_image' => $imagePath,
        ]);

        return true;
    }

    public function searchForProduct($items, $name)
    {
        return $this->productRepository->findByName($items, $name);
    }

    public function updateQuantity($storeId, $productId, $quantitySold): bool
    {

        $storeProduct = $this->productRepository->findStoreProductById($storeId, $productId);

        if (!$storeProduct || $storeProduct->quantity < $quantitySold) {
            return false;
        }

        $storeProduct->decrement('quantity', $quantitySold);
        $storeProduct->increment('sold_quantity', $quantitySold);

        return true;
    }

    public function updateProductDetails($store, $product, $data)
    {
        $storeProduct = $this->productRepository->findProductInStore($store, $product->id);

        if (!$storeProduct) {
            return null;
        }

        $fieldUpdaters = [
            'price' => fn($value) => $this->modifyPriceForProduct($storeProduct, $value),
            'quantity' => fn($value) => $this->addQuantityToStack($store, $product->id, $storeProduct, $value),
            'description_en' => fn($value) => $this->modifyDescriptionForProduct($storeProduct, $value, 'en'),
            'description_ar' => fn($value) => $this->modifyDescriptionForProduct($storeProduct, $value, 'ar'),
            'main_image' => fn($value) => $this->modifyMainImageForProduct($storeProduct, $value),
        ];

        foreach ($data as $key => $value) {
            if (!empty($value)) {
                $fieldUpdaters[$key]($value);
            }
        }

        return $storeProduct;
    }

    public function modifyMainImageForProduct(Store_Product $storeProduct, $image)
    {
        $imagePath = $this->productRepository->uploadImage($image, 'products');

        return $this->productRepository->updateProduct($storeProduct, ['main_image' => $imagePath]);
    }

    public function modifyPriceForProduct(Store_Product $storeProduct, $price)
    {
        return $this->productRepository->updateProduct($storeProduct, ['price' => $price]);
    }

    public function addQuantityToStack($store, $productId, $storeProduct, $quantity)
    {
        return $this->productRepository->incrementQuantity($store, $productId, $storeProduct, $quantity);
    }

    public function modifyDescriptionForProduct(Store_Product $storeProduct, $description, $lang)
    {
        return $this->productRepository->updateProduct($storeProduct, ['description_' . $lang => $description]);
    }

    public function deleteMainImage($image): void
    {
        if ((!empty($image)) && Storage::disk('public')->exists(path: $image)) {
            Storage::disk('public')->delete($image);
        }
    }

    public function deleteSubImages($images)
    {
        if ($images->isNotEmpty()) {
            foreach ($images as $image) {
                if (Storage::disk('public')->exists($image->image)) {
                    Storage::disk('public')->delete($image->image);
                }

                $image->delete();
            }
        }
    }
    public function removeProductFromStore(Store $store, Product $product)
    {
        $storeProduct = $this->productRepository->findStoreProductById($store->id, $product->id);

        if (!$storeProduct) {
            return false;
        }
        $this->deleteMainImage($storeProduct->main_image);
        $this->deleteSubImages($storeProduct->images);

        $storeProduct->delete();

        return true;
    }
}
