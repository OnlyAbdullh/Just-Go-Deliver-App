<?php

namespace App\Repositories;

use App\Models\Image;
use App\Repositories\Contracts\ImageRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ImageRepository implements ImageRepositoryInterface
{
    public function store($storeId, $productId, $images)
    {
        Log::info('from store function in image repo');

        foreach ($images as $image) {
            $imagePath = $image->store('products', 'public');

            Image::create([
                'store_id' => $storeId,
                'product_id' => $productId,
                'image' => $imagePath,
            ]);
        }
        Log::info('from store function in image repo after creating images');
    }

    public function update(Image $image, $newImage)
    {
        $image->update(['image' => $newImage]);
    }

    public function delete(Image $image)
    {
        $image->delete();
    }
}
