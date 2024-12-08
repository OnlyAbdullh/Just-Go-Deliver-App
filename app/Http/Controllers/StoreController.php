<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\CreateStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\User;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StoreController extends Controller
{
    private $storeSrevice;

    public function __construct(StoreService $storeService)
    {
        $this->storeSrevice = $storeService;
    }

    /**
     * @OA\Get(
     *     path="/api/stores",
     *     summary="Fetch paginated list of stores",
     *     description="Retrieve a paginated list of stores with their details, including manager, name, image URL, location, and description.",
     *     operationId="getStores",
     *     tags={"Stores"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to fetch",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="items",
     *         in="query",
     *         description="The number of items to display per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stores fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Stores fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="stores",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/StoreResource")
     *                 ),
     *                 @OA\Property(property="hasMorePages", type="boolean", example=true)
     *             ),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *         )
     *     ),
     *    @OA\Response(
     *         response=404,
     *         description="No stores available",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="There are no stores available"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {

        $itemsPerPage = $request->query('items', 10);

        $data = $this->storeSrevice->getAllStores($itemsPerPage);

        if (!$data) {
            return  JsonResponseHelper::errorResponse(__('messages.no_stores_available'), [], 404);
        }

        return JsonResponseHelper::successResponse(__('messages.stores_fetched'), $data);
    }


    /**
     * @OA\Post(
     *     path="/api/stores",
     *     summary="Create a new store",
     *     description="Creates a new store with the given details. The user must provide valid data, including a logo image, via multipart/form-data.",
     *     tags={"Stores"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="integer",
     *                     description="ID of the user creating the store",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="logo",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file for the store logo (jpg, png, jpeg)"
     *                 ),
     *                 @OA\Property(
     *                     property="location",
     *                     type="string",
     *                     description="Location of the store",
     *                     example="123 Main St, City, Country"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Description of the store",
     *                     example="A description of the store"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="Name of the store",
     *                     example="My Store"
     *                 ),
     *                 required={"user_id", "logo", "description", "name"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Store created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="store created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StoreResource"),
     *             @OA\Property(property="status_code", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(property="data", type="object",
     *                 additionalProperties={
     *                     @OA\Property(type="string", example="The user_id field is required.")
     *                 }
     *             ),
     *             @OA\Property(property="status_code", type="integer", example=400)
     *         )
     *     )
     * )
     */

    public function store(CreateStoreRequest $request): JsonResponse
    {
     /*   if (!Gate::allows('createStore', User::class)) {
            return JsonResponseHelper::successResponse(__('messages.store_admin_only_create'), [], 401);
        }*/

        $store = $this->storeSrevice->createStore($request->validated());

        return JsonResponseHelper::successResponse(__('messages.stores_created'), StoreResource::make($store), 201);
    }

    /**
     * @OA\Post(
     *     path="/api/stores/{store}",
     *     summary="Update an existing store",
     *     description="Update details of an existing store. Only authorized users can update their own stores. The request supports partial updates, including an optional logo file upload.",
     *     tags={"Stores"},
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         description="ID of the store to update",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="logo",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional image file for the store logo (jpg, png, jpeg)"
     *                 ),
     *                 @OA\Property(
     *                     property="location",
     *                     type="string",
     *                     description="Optional location of the store",
     *                     example="123 Main St, City, Country"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Optional description of the store",
     *                     example="A description of the store"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="Optional name of the store",
     *                     example="My Updated Store"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="store updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StoreResource"),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User not authorized to update this store",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not authorized to update this store."),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="status_code", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(property="data", type="object",
     *                 additionalProperties={
     *                     @OA\Property(type="string", example="The logo must be an image.")
     *                 }
     *             ),
     *             @OA\Property(property="status_code", type="integer", example=422)
     *         )
     *     )
     * )
     */

    public function update(UpdateStoreRequest $request, $storeId): JsonResponse
    {
        if (!Gate::allows('updateStore', User::class)) {
            return JsonResponseHelper::successResponse(__('messages.store_admin_only_update'), [], 401);
        }

        $store = $this->storeSrevice->updateStore($storeId, $request->validated());

        if (!$store) {
            return JsonResponseHelper::errorResponse(__('messages.store_not_found'), [], 404);
        }
        return JsonResponseHelper::successResponse(__('messages.store_updated'), StoreResource::make($store), 200);
    }

    /**
     * @OA\Get(
     *     path="/api/stores/{store}",
     *     summary="Get a specific store",
     *     description="Retrieve details of a specific store by its ID.",
     *     tags={"Stores"},
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         description="ID of the store to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="store displayed successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StoreResource"),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Store not found."),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */

    public function show(int $storeId): JsonResponse
    {

        $store = $this->storeSrevice->showStore($storeId);
        if (!$store) {
            return JsonResponseHelper::errorResponse(__('messages.store_not_found'), [], 404);
        }
        return JsonResponseHelper::successResponse(__('messages.store_displayed'), StoreResource::make($store), 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/stores/{store}",
     *     summary="Delete a specific store",
     *     description="Delete a store by its ID.",
     *     tags={"Stores"},
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         description="ID of the store to delete",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="store deleted successfully"),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Store not found."),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to delete the store",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not authorized to delete this store."),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="status_code", type="integer", example=403)
     *         )
     *     )
     * )
     */

    public function destroy(int $storeId): JsonResponse
    {
        if (!Gate::allows('deleteStore', User::class)) {
            return JsonResponseHelper::successResponse(__('messages.store_admin_only_delete'), [], 401);
        }

        $store = $this->storeSrevice->deleteStore($storeId);

        if (!$store) {
            return JsonResponseHelper::errorResponse(__('messages.store_not_found'), [], 404);
        }
        return JsonResponseHelper::successResponse(__('messages.store_deleted'), StoreResource::make($store), 200);
    }
}
