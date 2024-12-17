<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\UpdateImageRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Store;
use App\Models\Image;
use App\Services\ImageService;
use DragonCode\PrettyArray\Services\Formatters\Json;

class ImageController extends Controller
{
    private $imageService;
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    public function index()
    {
        //
    }

    public function store(Request $request, Store $store, Product $product) {
        //
    }
    
    public function update(UpdateImageRequest $request, Image $image)
    {
        $this->imageService->modifyImage($image, $request->file('image'));

        return JsonResponseHelper::successResponse('image update successfully');
    }
    public function destroy(Request $request, Image $image)
    {
        $ownerStore = $image->store->user_id;

        if (auth()->user()->hasRole('store_admin') && $ownerStore === auth()->id()) {
            $this->imageService->deleteImage($image);
            return JsonResponseHelper::successResponse('image delete successfully');
        }
        return JsonResponseHelper::successResponse('You are not authorized to delete image', [], 403);
    }
}
