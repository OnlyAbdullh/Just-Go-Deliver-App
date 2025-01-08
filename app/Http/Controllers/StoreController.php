<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\CreateStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Models\User;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your Bearer token in the format 'Bearer <token>'"
 * )
 */
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
     *
     * @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to fetch",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="items",
     *         in="query",
     *         description="The number of items to display per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Stores fetched successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Stores fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="stores",
     *                     type="array",
     *
     *                     @OA\Items(ref="#/components/schemas/StoreResource")
     *                 )
     *             ),
     *
     *             @OA\Property(property="status_code", type="integer", example=200),
     * *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(
     *                         property="currentPage",
     *                         type="integer",
     *                         description="The current page number",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="totalPages",
     *                         type="integer",
     *                         description="The total number of pages",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="totalItems",
     *                         type="integer",
     *                         description="The total number of pages",
     *                         example=23
     *                     ),
     *                     @OA\Property(
     *                         property="hasMorePage",
     *                         type="boolean",
     *                         description="Indicates if there are more pages available",
     *                         example=false
     *                     )
     *                 )
     *         )
     *     ),
     *
     *    @OA\Response(
     *         response=404,
     *         description="No stores available",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="There are no stores available"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language');
        $itemsPerPage = $request->query('items', 10);

        $stores = $this->storeSrevice->getAllStores($itemsPerPage);

        return response()->json([
            'successful' => true,
            'message' => __('messages.retrieve_all_stores'),
            'data' => [
                'stores' => StoreResource::collection($stores),
            ],
            'pagination' => [
                'currentPage' => $stores->currentPage(),
                'totalPages' => $stores->lastPage(),
                'totalItems' => $stores->total(),
                'hasMorePage' => $stores->hasMorePages(),
            ],
            'statuc_code' => 200,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/stores",
     *     summary="Create a new store",
     *     description="Creates a new store with the given details. The user must provide valid data, including a logo image, via multipart/form-data.",
     *     tags={"Stores"},
     *     security={{"bearerAuth": {}}},
     *
     *      @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"logo", "name_ar", "name_en"},
     *
     *                 @OA\Property(
     *                     property="logo",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file for the store logo (jpg, png, jpeg)",
     *                     example="logo.jpg"
     *                 ),
     *                 @OA\Property(
     *                     property="location_ar",
     *                     type="string",
     *                     description="Arabic location of the store (optional)",
     *                     example="123 شارع رئيسي"
     *                 ),
     *                 @OA\Property(
     *                     property="location_en",
     *                     type="string",
     *                     description="English location of the store (optional)",
     *                     example="123 Main St, City, Country"
     *                 ),
     *                 @OA\Property(
     *                     property="description_ar",
     *                     type="string",
     *                     description="Arabic description of the store (optional)",
     *                     example="وصف المتجر"
     *                 ),
     *                 @OA\Property(
     *                     property="name_ar",
     *                     type="string",
     *                     description="Arabic name of the store (required, must be unique)",
     *                     example="متجري"
     *                 ),
     *                 @OA\Property(
     *                     property="description_en",
     *                     type="string",
     *                     description="English description of the store (optional)",
     *                     example="A description of the store"
     *                 ),
     *                 @OA\Property(
     *                     property="name_en",
     *                     type="string",
     *                     description="English name of the store (required, must be unique)",
     *                     example="My Store"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Store created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Store created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StoreResource"),
     *             @OA\Property(property="status_code", type="integer", example=201)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 additionalProperties=@OA\Property(type="string", example="The name_ar field is required.")
     *             ),
     *             @OA\Property(property="status_code", type="integer", example=400)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Authorization Error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You already have a store and cannot create another one."),
     *             @OA\Property(property="status_code", type="integer", example=401)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only store admin can create a store"),
     *             @OA\Property(property="status_code", type="integer", example=403)
     *         )
     *     )
     * )
     */
    public function store(CreateStoreRequest $request)
    {
        if (auth()->user()->store) {
            return JsonResponseHelper::errorResponse(__('messages.store_already_exists'), [], 401);
        }

        $store = $this->storeSrevice->createStore($request->validated());

        return JsonResponseHelper::successResponse(__('messages.stores_created'), StoreResource::make($store), 201);
    }

    /**
     * @OA\Post(
     *     path="/api/stores/{store}",
     *     summary="Update an existing store",
     *     description="Update details of an existing store. Only authorized users can update their own stores. The request supports partial updates, including an optional logo file upload.",
     *     tags={"Stores"},
     *     security={{"bearerAuth": {}}},
     *
     * @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         description="ID of the store to update",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *      @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"logo", "name_ar", "name_en"},
     *
     *                 @OA\Property(
     *                     property="logo",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file for the store logo (jpg, png, jpeg)",
     *                     example="logo.jpg"
     *                 ),
     *                 @OA\Property(
     *                     property="location_ar",
     *                     type="string",
     *                     description="Arabic location of the store (optional)",
     *                     example="123 شارع رئيسي"
     *                 ),
     *                 @OA\Property(
     *                     property="location_en",
     *                     type="string",
     *                     description="English location of the store (optional)",
     *                     example="123 Main St, City, Country"
     *                 ),
     *                 @OA\Property(
     *                     property="description_ar",
     *                     type="string",
     *                     description="Arabic description of the store (optional)",
     *                     example="وصف المتجر"
     *                 ),
     *                 @OA\Property(
     *                     property="name_ar",
     *                     type="string",
     *                     description="Arabic name of the store (required, must be unique)",
     *                     example="متجري"
     *                 ),
     *                 @OA\Property(
     *                     property="description_en",
     *                     type="string",
     *                     description="English description of the store (optional)",
     *                     example="A description of the store"
     *                 ),
     *                 @OA\Property(
     *                     property="name_en",
     *                     type="string",
     *                     description="English name of the store (required, must be unique)",
     *                     example="My Store"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Store updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Store updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StoreResource"),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User not authorized to update this store",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not authorized to update this store."),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="status_code", type="integer", example=403)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(property="error", type="object",
     *
     *                 @OA\AdditionalProperties(
     *
     *                     @OA\Schema(type="string", example="The logo must be an image.")
     *                 )
     *             ),
     *
     *             @OA\Property(property="status_code", type="integer", example=400)
     *         )
     *     )
     * )
     */
    public function update(UpdateStoreRequest $request, Store $store)
    {
        $store = $this->storeSrevice->updateStore($store, $request->validated());

        return JsonResponseHelper::successResponse(__('messages.store_updated'), StoreResource::make($store), 200);
    }

    /**
     * @OA\Get(
     *     path="/api/stores/{store}/show",
     *     summary="Get a specific store",
     *     description="Retrieve details of a specific store by its ID.",
     *     tags={"Stores"},
     *
     * @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         description="ID of the store to retrieve",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Store retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="store displayed successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StoreResource"),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Store not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Store not found."),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function show(Store $store)
    {
        $store = $this->storeSrevice->getStore($store->id);

        return JsonResponseHelper::successResponse(__('messages.store_displayed'), $store, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/stores/search/{name}",
     *     summary="Search stores by name",
     *     description="Search for stores by name and retrieve a paginated list of stores along with their manager's name, description, and location.",
     *     tags={"Stores"},
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of the store to search for",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="items",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="integer",
     *             default=20
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of stores with pagination",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="successful",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Retrieve all stores"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="stores",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(
     *                             property="store_id",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="name",
     *                             type="string",
     *                             example="Store 1"
     *                         ),
     *                         @OA\Property(
     *                             property="image_url",
     *                             type="string",
     *                             example="http://example.com/storage/logo1.png"
     *                         ),
     *                         @OA\Property(
     *                             property="description",
     *                             type="string",
     *                             example="Description of store 1"
     *                         ),
     *                         @OA\Property(
     *                             property="location",
     *                             type="string",
     *                             example="Location 1"
     *                         ),
     *                         @OA\Property(
     *                             property="manager",
     *                             type="string",
     *                             example="John Doe"
     *                         )
     *                     )
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(
     *                     property="currentPage",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="totalPages",
     *                     type="integer",
     *                     example=5
     *                 ),
     *                 @OA\Property(
     *                     property="totalItems",
     *                     type="integer",
     *                     example=100
     *                 ),
     *                 @OA\Property(
     *                     property="hasMorePage",
     *                     type="boolean",
     *                     example=true
     *                 )
     *             ),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *     ),
     * )
     */
    public function search(Request $request, $name)
    {
        $items = $request->query('items', 20);

        $stores = $this->storeSrevice->searchForStore($name, $items);

        return response()->json([
            'successful' => true,
            'message' => __('messages.retrieve_all_stores'),
            'data' => [
                'stores' => $stores->items(),
            ],
            'pagination' => [
                'currentPage' => $stores->currentPage(),
                'totalPages' => $stores->lastPage(),
                'totalItems' => $stores->total(),
                'hasMorePage' => $stores->hasMorePages(),
            ],
            'statuc_code' => 200,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/stores/{store}/delete",
     *     summary="Delete a specific store",
     *     description="Delete a store by its ID.",
     *     tags={"Stores"},
     *     security={{"bearerAuth": {}}},
     *
     * @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         description="ID of the store to delete",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Store deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Store deleted successfully"),
     *             @OA\Property(property="data", type="object", example={} ),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Store not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Store not found."),
     *             @OA\Property(property="data", type="object", example={} ),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to delete the store",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not authorized to delete this store."),
     *             @OA\Property(property="data", type="object", example={} ),
     *             @OA\Property(property="status_code", type="integer", example=403)
     *         )
     *     )
     * )
     */
    public function destroy(Store $store): JsonResponse
    {
        if (! auth()->user()->hasRole('store_admin') || $store->user_id !== auth()->id()) {
            return JsonResponseHelper::errorResponse(__('messages.store_delete_unauthorized'), [], 403);
        }

        $this->storeSrevice->deleteStore($store);

        return JsonResponseHelper::successResponse(__('messages.store_deleted'), [], 200);
    }
}
