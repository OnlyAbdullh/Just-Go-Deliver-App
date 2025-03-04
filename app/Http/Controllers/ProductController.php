<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\createProductRequest;
use App\Http\Requests\updateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use App\Repositories\Contracts\StoreRepositoryInterface;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productService;

    private $storeRepository;

    public function __construct(ProductService $productService, StoreRepositoryInterface $storeRepository)
    {
        $this->productService = $productService;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Retrieve all products",
     *     description="Returns a paginated list of products with their store and category information.",
     *     tags={"Products"},
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
     *         name="items",
     *         in="query",
     *         required=false,
     *         description="Number of items per page",
     *
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="A list of products with pagination information",
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
     *                 example="retrieve all products"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         ref="#/components/schemas/ProductResource"
     *                     )
     *                 ),
     *
     *                 @OA\Property(
     *                 property="status_code",
     *                 type="integer",
     *                 example=200
     *                 ),
     *                  @OA\Property(
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
     *                         example=22
     *                     ),
     *                     @OA\Property(
     *                         property="hasMorePage",
     *                         type="boolean",
     *                         description="Indicates if there are more pages available",
     *                         example=false
     *                     )
     *                 )
     *             ),
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        // $lang = $request->header('Accept-Language', 'en');
        $items = $request->query('items', 20);

        $products = $this->productService->getAllProduct($items);

        return response()->json([
            'successful' => true,
            'message' => __('messages.retrieve_all_products_success'),
            'data' => [
                'products' => ProductResource::collection($products),
            ],
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'totalPages' => $products->lastPage(),
                'totalItems' => $products->total(),
                'hasMorePage' => $products->hasMorePages(),
            ],
            'status_code' => 200,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/stores/{store}/{product}/show",
     *     summary="Retrieve product details for a specific store",
     *     description="Returns product details, including store information and sub_images (additional images).",
     *     operationId="getStoreProduct",
     *     tags={"Products"},
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
     *         name="store_id",
     *         in="path",
     *         required=true,
     *         description="The ID of the store",
     *
     *         @OA\Schema(type="integer", example=6)
     *     ),
     *
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="The ID of the product",
     *
     *         @OA\Schema(type="integer", example=7)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="retrieve product successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="store_id", type="integer", example=6),
     *                 @OA\Property(property="store_name", type="string", example="متجر حسووون"),
     *                 @OA\Property(property="product_id", type="integer", example=7),
     *                 @OA\Property(property="product_name", type="string", example="clock"),
     *                 @OA\Property(property="price", type="string", example="20.00"),
     *                 @OA\Property(property="quantity", type="integer", example=5),
     *                 @OA\Property(property="description", type="string", example="black color"),
     *                 @OA\Property(property="main_image", type="string", format="url",
     *                     example="http://127.0.0.1:8000/products/XwLnjmoXymmCefQwKIxmYfKpVuKXjstUKGRcIM9a.jpg"
     *                 ),
     *                 @OA\Property(property="sub_images", type="array",
     *
     *                     @OA\Items(
     *
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="image", type="string", format="url",
     *                             example="http://127.0.0.1:8000/products/rJl1XLR4FdCGy6NXciZ0ZrCY20DTVtgnSUTb1Awl.png"
     *                         )
     *                     )
     *                 ),
     *
     *             ),
     *  @OA\Property(property="status_code", type="integer", example=200),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Product not found in store",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found in store"),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *         )
     *     )
     * )
     */
    public function show(Store $store, Product $product)
    {
        $product = $this->productService->showProduct($store, $product);

        if (! $product) {
            return JsonResponseHelper::errorResponse(__('messages.product_not_found_in_store'), [], 404);
        }

        return JsonResponseHelper::successResponse(__('messages.retrieve_product_successfully'), ProductResource::make($product));
    }

    /**
     * @OA\Post(
     *     path="/stores/{store}",
     *     summary="Add a product to a store",
     *     description="Adds a new product to a store by the store's owner",
     *     operationId="addProductToStore",
     *     tags={"Products"},
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
     *         description="ID of the store",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"name_ar", "name_en", "category_name_ar", "category_name_en", "main_image", "sub_images", "price", "quantity", "description_ar", "description_en"},
     *
     *                 @OA\Property(property="name_ar", type="string", description="Name of the product in Arabic"),
     *                 @OA\Property(property="name_en", type="string", description="Name of the product in English"),
     *                 @OA\Property(property="category_name_ar", type="string", description="Category name in Arabic"),
     *                 @OA\Property(property="category_name_en", type="string", description="Category name in English"),
     *                 @OA\Property(property="main_image", type="string", format="binary", description="Main image of the product (JPEG, PNG, JPG, BMP) with max size 2MB"),
     *                 @OA\Property(property="sub_images", type="array", description="Sub images of the product (JPEG, PNG, JPG, BMP) with max size 2MB each",
     *
     *                     @OA\Items(type="string", format="binary")
     *                 ),
     *
     *                 @OA\Property(property="price", type="number", format="float", description="Price of the product"),
     *                 @OA\Property(property="quantity", type="integer", description="Available quantity of the product"),
     *                 @OA\Property(property="description_ar", type="string", description="Description of the product in Arabic"),
     *                 @OA\Property(property="description_en", type="string", description="Description of the product in English")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Product added successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product added to store successfully"),
     *             @OA\Property(property="data", type="object", example={} ),
     *             @OA\Property(property="status_code", type="integer", example=201)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized to add product to this store",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not authorized to add a product to this store."),
     *             @OA\Property(property="data", type="object", example={} ),
     *             @OA\Property(property="status_code", type="integer", example=401)
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
     *             @OA\Property(property="message", type="string", example="Store not found"),
     *             @OA\Property(property="data", type="object", example={} ),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function store(createProductRequest $request, Store $store)
    {
        $validated = $request->validated();

        $this->productService->addProductToStore($validated, $store);

        return JsonResponseHelper::successResponse(__('messages.product_added_success'), [], 201);
    }

    /**
     * @OA\Post(
     *     path="api/stores/{store}/{product}",
     *     summary="Update a product in a store",
     *     description="Update product details for a store. Only accessible by users with the store_admin role who own the store.",
     *     tags={"Products"},
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
     *         description="ID of the store",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID of the product to be updated",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="price",
     *                     type="number",
     *                     format="float",
     *                     example=199.99,
     *                     description="New price of the product"
     *                 ),
     *                 @OA\Property(
     *                     property="quantity",
     *                     type="integer",
     *                     example=10,
     *                     description="New quantity of the product"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     example="Updated product description",
     *                     description="New description of the product"
     *                 ),
     *                 @OA\Property(
     *                     property="main_image",
     *                     type="string",
     *                     format="binary",
     *                     description="Main image for the product"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product details updated successfully."),
     *             @OA\Property(property="data", type="object", description="Updated product details"),
     *              @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not authorized to update a product in this store"),
     *             @OA\Property(property="status_code", type="integer", example=403)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Product not found or Store Not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object"),
     *              @OA\Property(property="status_code", type="integer", example=400)
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function update(updateProductRequest $request, Store $store, Product $product)
    {
        $validated = $request->validated();

        $result = $this->productService->updateProductDetails($store, $product, $validated);

        if ($result) {
            return JsonResponseHelper::successResponse(__('messages.product_update_success'));
        }

        return JsonResponseHelper::errorResponse(__('messages.product_not_found_in_store'), [], 404);
    }

    /**
     * @OA\Delete(
     *     path="/api/stores/{store}/{product}/delete",
     *     summary="Delete a product from a store",
     *     description="Allows a store admin to delete a specific product from their store.",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     security={{"bearerAuth":{}}},
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
     *         description="ID of the store",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID of the product",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="integer",
     *             example=10
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="The product was successfully deleted from the store."),
     *            @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to delete the product",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to delete this product."),
     *             @OA\Property(property="status_code", type="integer", example=403)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Product not found or Store not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="successful", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="The product was not found in this store."),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function destroy(Store $store, Product $product)
    {
        if ((! auth()->user()->hasRole('store_admin')) || $store->user_id !== auth()->id()) {
            return JsonResponseHelper::errorResponse(__('messages.not_authorized_to_delete_product'), [], 403);
        }

        $result = $this->productService->removeProductFromStore($store, $product);

        if ($result) {
            return JsonResponseHelper::successResponse(__('messages.product_deleted_successfully'));
        }

        return JsonResponseHelper::errorResponse(__('messages.product_not_found_in_store'), [], 404);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{name}",
     *     summary="Search for products by name",
     *     description="Retrieve a paginated list of products based on the search keyword",
     *     tags={"Products"},
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
     *         name="name",
     *         in="path",
     *         description="The name of the product to search for",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="items",
     *         in="query",
     *         description="Number of items per page (default is 20)",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="integer",
     *             default=20
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number for pagination (default is 1)",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="A list of products with pagination",
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
     *                 example="Products retrieved successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *
     *                     @OA\Items(ref="#/components/schemas/ProductResource")
     *                 )
     *             ),
     *
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
     *                     example=5
     *                 ),
     *                 @OA\Property(
     *                     property="hasMorePage",
     *                     type="boolean",
     *                     example=true
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request parameters"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No products found matching the search criteria"
     *     )
     * )
     */
    public function search(Request $request, $name)
    {
        $itemsPerPage = $request->query('items', 20);

        $products = $this->productService->searchForProduct($itemsPerPage, $name);

        if ($products->isEmpty()) {
            return response()->json([
                'successful' => false,
                'message' => __('messages.no_products_available'),
                'data' => [],
                'status_code' => 404,
            ]);
        }

        return response()->json([
            'successful' => true,
            'message' => __('messages.retrieve_all_products_success'),
            'data' => [
                'products' => ProductResource::collection($products),
            ],
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'totalPages' => $products->lastPage(),
                'totalItems' => $products->total(),
                'hasMorePage' => $products->hasMorePages(),
            ],
            'status_code' => 200,
        ]);
    }
}
