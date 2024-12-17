<?php

namespace App\Services;

use App\Models\Image;
use App\Repositories\ImageRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    private $imageRepository;
    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }
    public function modifyImage(Image $image, UploadedFile $newImage)
    {
        if ((!empty($image->image)) && Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }

        $imagePath = $newImage->store($image, 'public');

        $this->imageRepository->update($image, $imagePath);

        return true;
    }

    public function deleteImage(Image $image)
    {
        
        $this->imageRepository->delete($image);
    }
}
