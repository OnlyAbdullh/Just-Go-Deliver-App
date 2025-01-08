<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\ProductDetailResource;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashBoardController extends Controller
{
    private $dashboardService;
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/{user}",
     *     summary="Retrieve all products for a store",
     *     description="Get a paginated list of all products belonging to the store associated with the given user.",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user whose store products are to be retrieved",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="items",
     *         in="query",
     *         description="Number of products to retrieve per page",
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
     *         description="List of products with pagination",
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
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(
     *                             property="store_product_id",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="product_id",
     *                             type="integer",
     *                             example=101
     *                         ),
     *                         @OA\Property(
     *                             property="product_name",
     *                             type="string",
     *                             example="Product 1"
     *                         ),
     *                         @OA\Property(
     *                             property="category_id",
     *                             type="integer",
     *                             example=10
     *                         ),
     *                         @OA\Property(
     *                             property="category_name",
     *                             type="string",
     *                             example="Category 1"
     *                         ),
     *                         @OA\Property(
     *                             property="main_image",
     *                             type="string",
     *                             example="http://example.com/images/product1.jpg"
     *                         ),
     *                         @OA\Property(
     *                             property="price",
     *                             type="number",
     *                             format="float",
     *                             example=100.50
     *                         ),
     *                         @OA\Property(
     *                             property="quantity",
     *                             type="integer",
     *                             example=20
     *                         ),
     *                         @OA\Property(
     *                             property="description",
     *                             type="string",
     *                             example="This is a product description"
     *                         )
     *                     )
     *                 )
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
     *             @OA\Property(
     *                 property="status_code",
     *                 type="integer",
     *                 example=200
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Store not found for the given user or user not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="successful",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Store not found"
     *             ),
     *             @OA\Property(
     *                 property="status_code",
     *                 type="integer",
     *                 example=404
     *             )
     *         )
     *     )
     * )
     */
    public function getProducts(Request $request, User $user)
    {
        $items = $request->query('items', 20);

        if (! $store = $user->store) {
            return JsonResponseHelper::errorResponse(__('messages.no_store'), [], 404);
        }

        $products = $this->dashboardService->getAllProductForStore($items, $store->id);

        return response()->json([
            'successful' => true,
            'message' => __('messages.retrieve_all_products_success'),
            'data' => [
                'products' => ProductDetailResource::collection($products),
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
     *     path="/api/dashboard/product-statistics",
     *     summary="Retrieve paginated product statistics for the store",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
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
     *         description="Number of items per page for pagination (default: 20)",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of product statistics",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Products retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="store_product_id", type="integer", example=1),
     *                         @OA\Property(property="main_image", type="string", example="http://example.com/storage/image.jpg"),
     *                         @OA\Property(property="price", type="number", format="float", example=99.99),
     *                         @OA\Property(property="quantity", type="integer", example=50),
     *                         @OA\Property(property="description", type="string", example="Product description in the current language."),
     *                         @OA\Property(property="product_id", type="integer", example=2),
     *                         @OA\Property(property="product_name", type="string", example="Product Name"),
     *                         @OA\Property(property="category_id", type="integer", example=3),
     *                         @OA\Property(property="category_name", type="string", example="Category Name"),
     *                         @OA\Property(property="total_quantity_sold", type="integer", example=100),
     *                         @OA\Property(
     *                             property="users_who_bought",
     *                             type="array",
     *
     *                             @OA\Items(type="string", example="John Doe")
     *                         )
     *                     )
     *                 )
     *             ),
     *
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="currentPage", type="integer", example=1),
     *                 @OA\Property(property="totalPages", type="integer", example=5),
     *                 @OA\Property(property="totalItems", type="integer", example=100),
     *                 @OA\Property(property="hasMorePage", type="boolean", example=true)
     *             ),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Store not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No store found."),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function getProductStatistics(Request $request, User $user)
    {
        $items = $request->query('items', 20);

        if (! $store = $user->store) {
            return JsonResponseHelper::errorResponse(__('messages.no_store'), [], 404);
        }

        $products = $this->dashboardService->getProductStatistics($items, $store->id);

        return response()->json([
            'successful' => true,
            'message' => __('messages.retrieve_all_products_success'),
            'data' => [
                'products' => $products->items(),
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

    public function getOrdersForStore(Request $request)
    {
        if (! $store = auth()->user()->store) {
            return JsonResponseHelper::errorResponse(__('messages.no_store'), [], 404);
        }

        return DB::table('orders')
            ->join('order_products', 'orders.id', '=', 'order_products.order_id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('store_products', 'order_products.store_product_id', '=', 'store_products.id')
            ->where('store_products.id', $store->id)
            ->select([
                'orders.id',
                'order_products.store_product_id',
                'orders.status',
                'order_products.quantity',
                'orders.total_price',
                'orders.order_date',
                DB::raw('GROUP_CONCAT(CONCAT(users.first_name, " ", users.last_name) SEPARATOR ", ") as owner_order'),
            ])->groupBy(
                'orders.id',
                'order_products.store_product_id',
                'orders.status',
                'order_products.quantity',
                'orders.total_price',
                'orders.order_date'
            )
            ->orderBy('orders.order_date', 'asc')
            ->get();
    }

    public function updateOrderStatus(UpdateOrderStatusRequest $request)
    {
        $validated = $request->validated();

        $order = DB::table('orders')
            ->where('orders.id', $validated['order_id'])
            ->where('orders.user_id', $validated['seller_id'])
            ->select('orders.id', 'users.id')
            ->update([
                'status' => $validated['status'],
                'updated_at' => Carbon::now(),
            ]);

        if (! $order) {
            return JsonResponseHelper::errorResponse(__('messages.order_not_found'), [], 404);
        }

        return JsonResponseHelper::successResponse(__('messages.order_updated', [], 200));
    }
}
