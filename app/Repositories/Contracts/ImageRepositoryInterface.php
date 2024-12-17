<?php

namespace App\Repositories\Contracts;

use App\Models\Image;

interface ImageRepositoryInterface
{
    public function store($storeId, $productId, $images);
    public function update(Image $image, $newImage);
    public function delete(Image $image);
}
