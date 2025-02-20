<?php

namespace App\Repositories;

use App\Models\Image;
use App\Repositories\Contracts\ImageRepositoryInterface;

class ImageRepository implements ImageRepositoryInterface
{
    public function store($storeId, $productId, $images)
    {

        foreach ($images as $image) {
            $imagePath = $image->store('products', 'public');

            Image::create([
                'store_id' => $storeId,
                'product_id' => $productId,
                'image' => $imagePath,
            ]);
        }
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
