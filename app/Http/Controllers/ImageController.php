<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\CreateImageRequest;
use App\Http\Requests\UpdateImageRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Store;
use App\Models\Image;
use App\Services\ImageService;
use DragonCode\PrettyArray\Services\Formatters\Json;
use Illuminate\Support\Facades\Log;

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

    /**
     * @OA\Post(
     *     path="/images/stores/{store}/products/{product}",
     *     summary="Add an image to a product in a store",
     *     description="This endpoint allows a store admin to upload an image for a specific product in a store.",
     *     operationId="addImageToProduct",
     *     tags={"Images"},
     *     security={{"bearerAuth":{}}},
     *  @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         required=true,
     *         description="ID of the store",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="ID of the product",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"images"},
     *                 @OA\Property(
     *                     property="images[0]",
     *                     description="Image file to upload",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="images[1]",
     *                     description="Image file to upload",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Image added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="Image added successfully"),
     *             @OA\Property(property="data", type="object"),
     *            @OA\Property(property="status_code", type="integer", example="201"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to add image",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to add image"),
     *            @OA\Property(property="status_code", type="integer", example="403"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="array", @OA\Items(
     *                 type="string",
     *                 example="The image field is required."
     *             )),
     *            @OA\Property(property="status_code", type="integer", example="400"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found in store",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Store not found or Product not found in store"),
     *             @OA\Property(property="status_code", type="integer", example="404"),
     *         )
     *     )
     * )
     */

    public function store(CreateImageRequest $request, Store $store, Product $product)
    {
        $result = $this->imageService->addImage($store, $product, $request->file('images'));

        if ($result) {
            return JsonResponseHelper::successResponse(__('messages.image_added_successfully'), [], 201);
        }
        return JsonResponseHelper::errorResponse(__('messages.product_not_found_in_store'), [], 404);
    }

    /**
     * @OA\Post(
     *     path="/images/{image}",
     *     summary="Update an image",
     *     description="This endpoint allows a store admin to update an existing image.",
     *     operationId="updateImage",
     *     tags={"Images"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *     @OA\Parameter(
     *         name="image",
     *         in="path",
     *         required=true,
     *         description="ID of the image to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"image"},
     *                 @OA\Property(
     *                     property="image",
     *                     description="New image file to upload",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Image updated successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to update image",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not authorized to update image"),
     *             @OA\Property(property="status_code", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="array", @OA\Items(
     *                 type="string",
     *                 example="The image field is required."
     *             )),
     *             @OA\Property(property="status_code", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Image not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Image not found"),
     *             @OA\Property(property="status_code", type="integer", example=404) 
     *         )
     *     )
     * )
     */

    public function update(UpdateImageRequest $request, Image $image)
    {
        $this->imageService->modifyImage($image, $request->file('image'));

        return JsonResponseHelper::successResponse(__('messages.image_updated'));
    }

    /**
     * @OA\Delete(
     *     path="/images/{image}",
     *     summary="Delete an image",
     *     description="This endpoint allows a store admin to delete an image if authorized.",
     *     operationId="deleteImage",
     *     tags={"Images"},
     *     security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *     @OA\Parameter(
     *         name="image",
     *         in="path",
     *         required=true,
     *         description="ID of the image to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Image deleted successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to delete image",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not authorized to delete image"),
     *             @OA\Property(property="status_code", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Image not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Image not found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */

    public function destroy(Request $request, Image $image)
    {
        $ownerStore = $image->store->user_id;

        if (auth()->user()->hasRole('store_admin') && $ownerStore === auth()->id()) {
            $this->imageService->deleteImage($image);
            return JsonResponseHelper::successResponse(__('messages.image_deleted'));
        }
        return JsonResponseHelper::successResponse(__('messages.not_authorized_to_delete_image'), [], 403);
    }
}
