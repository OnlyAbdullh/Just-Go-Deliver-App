<?php

namespace App\Services;

use App\Models\Image;
use App\Models\Product;
use App\Models\Store;
use App\Repositories\Contracts\ImageRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\ImageRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    private $imageRepository, $productRepository;
    public function __construct(ImageRepositoryInterface $imageRepository, ProductRepositoryInterface $productRepository)
    {
        $this->imageRepository = $imageRepository;
        $this->productRepository = $productRepository;
    }

    public function addImage(Store $store, Product $product, $image)
    {
        $product = $this->productRepository->findProductInStore($store, $product->id);
        if (!$product) {
            return false;
        }

        $this->imageRepository->store($store->id, $product->id, $image);

        return true;
    }
    public function modifyImage(Image $image, UploadedFile $newImage)
    {
        if (Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }

        $imagePath = $newImage->store('products', 'public');

        $this->imageRepository->update($image, $imagePath);

        return true;
    }

    public function deleteImage(Image $image)
    {
        if ((!empty($image->image)) && Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }

        $this->imageRepository->delete($image);
    }
}
