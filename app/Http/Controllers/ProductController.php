<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Repositories\Contracts\StoreRepositoryInterface;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Services\StoreService;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    private $productService, $storeRepository;

    public function __construct(ProductService $productService, StoreRepositoryInterface $storeRepository)
    {
        $this->productService = $productService;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @OA\Post(
     *     path="/stores/{storeId}/products",
     *     summary="Add a product to a store",
     *     description="Adds a new product to a store by the store's owner",
     *     operationId="addProductToStore",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="storeId",
     *         in="path",
     *         description="ID of the store",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "main_image", "price", "quantity", "description"},
     *                 @OA\Property(property="name", type="string", description="Name of the product"),
     *                 @OA\Property(property="main_image", type="string", format="binary", description="Main image of the product"),
     *                 @OA\Property(property="price", type="number", format="float", description="Price of the product"),
     *                 @OA\Property(property="quantity", type="integer", description="Available quantity of the product"),
     *                 @OA\Property(property="description", type="string", description="Description of the product")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product added to store successfully"),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="status_code", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized to add product to this store",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not authorized to add a product to this store."),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="status_code", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Store not found"),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */

    public function store(Request $request, $storeId)
    {

        $store = $this->storeRepository->findById($storeId);

        if (!$store) {
            return JsonResponseHelper::successResponse(__('messages.store_not_found'), [], 404);
        }

        // if (!Gate::allows('addProductToStore', $store)) {
        //     return JsonResponseHelper::successResponse(__('messages.not_authorized_to_add_product'), [], 401);
        // }


        $validated = $request->validate([
            'name' => 'required',
            'category_name' => 'required',
            'main_image' => 'required|image',
            'price' => 'required',
            'quantity' => 'required',
            'description' => 'required',
        ]);

        $result = $this->productService->addProductToStore($validated, $store);

        if ($result) {
            return JsonResponseHelper::successResponse('Product added to store successfully', [], 201);
        }
    }

    public function update(Request $request, $storeId, $productId)
    {
        $store = $this->storeRepository->findById($storeId);
        if (!$store) {
            return JsonResponseHelper::successResponse(__('messages.store_not_found'), [], 404);
        }

        if (!Gate::allows('updateProduct', $store)) {
            return JsonResponseHelper::successResponse(__('messages.not_authorized_to_update_product'), [], 401);
        }
        
        $validated = $request->validate([
            'price' => 'sometimes|numeric|between:0,99999.99',
            'quantity' => 'sometimes',
            'description' => 'sometimes',
            'main_image' => 'sometimes|image',
        ]);

        $result = $this->productService->updateProductDetails($storeId, $productId, $validated);

        if ($result) {
            return JsonResponseHelper::successResponse('Product details updated successfully.', $result);
        }


        return JsonResponseHelper::successResponse('Product not found or update failed', [], 404);
    }
}
