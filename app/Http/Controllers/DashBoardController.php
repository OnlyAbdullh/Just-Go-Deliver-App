<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;
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
     *     path="/api/dashboard",
     *     summary="Retrieve all products for a store",
     *     description="Get a list of all products belonging to the store associated with the given user.",
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
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of products with store details",
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
     *                 example="تم جلب جميع المنتجات بنجاح"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=22
     *                     ),
     *                     @OA\Property(
     *                         property="manager",
     *                         type="string",
     *                         example="hasan zaeter"
     *                     ),
     *                     @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         example="hasan@gmail.com"
     *                     ),
     *                     @OA\Property(
     *                         property="phone_number",
     *                         type="string",
     *                         example="0935917556"
     *                     ),
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="النضر"
     *                     ),
     *                     @OA\Property(
     *                         property="image_url",
     *                         type="string",
     *                         example="http://127.0.0.1:8000/storage/stores/FaZiIVnwZA66POD9BQ40rEc71mfCdHsouTTmZIYu.jpg"
     *                     ),
     *                     @OA\Property(
     *                         property="location",
     *                         type="string",
     *                         example="ميدان"
     *                     ),
     *                     @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         example="أفضل متجر"
     *                     ),
     *                     @OA\Property(
     *                         property="products",
     *                         type="array",
     *
     *                         @OA\Items(
     *                             type="object",
     *
     *                             @OA\Property(
     *                                 property="store_id",
     *                                 type="integer",
     *                                 example=11
     *                             ),
     *                             @OA\Property(
     *                                 property="store_name",
     *                                 type="string",
     *                                 example="النضر"
     *                             ),
     *                             @OA\Property(
     *                                 property="product_id",
     *                                 type="integer",
     *                                 example=101
     *                             ),
     *                             @OA\Property(
     *                                 property="product_name",
     *                                 type="string",
     *                                 example="كيبورد"
     *                             ),
     *                             @OA\Property(
     *                                 property="main_image",
     *                                 type="string",
     *                                 example="http://127.0.0.1:8000/storage/products/0hQqWfvMzKmP3SLyeh0JFUedUMLVTlNEWzMX4f5R.jpg"
     *                             ),
     *                             @OA\Property(
     *                                 property="price",
     *                                 type="string",
     *                                 example="1200.00"
     *                             ),
     *                             @OA\Property(
     *                                 property="quantity",
     *                                 type="integer",
     *                                 example=10
     *                             ),
     *                             @OA\Property(
     *                                 property="description",
     *                                 type="string",
     *                                 example="كيبورد غيمنغ"
     *                             ),
     *                             @OA\Property(
     *                                 property="is_favorite",
     *                                 type="integer",
     *                                 example=0
     *                             ),
     *                             @OA\Property(
     *                                 property="category_id",
     *                                 type="integer",
     *                                 example=4
     *                             ),
     *                             @OA\Property(
     *                                 property="category_name",
     *                                 type="string",
     *                                 example="اجهزة"
     *                             )
     *                         )
     *                     )
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
    public function getProducts(Request $request)
    {
        if (! $store = auth()->user()->store) {
            return JsonResponseHelper::errorResponse(__('messages.no_store'), [], 404);
        }

        $products = $this->dashboardService->getAllProductForStore($store->id);

        return JsonResponseHelper::successResponse(__('messages.retrieve_all_products_success'), $products);
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

    /**
     * @OA\Get(
     *     path="/api/dashboard/orders",
     *     summary="Get orders for the authenticated user's store",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Orders fetched successfully",
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
     *                 example="تم جلب الطلبات بنجاح"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="order_date", type="string", example="2024-01-01"),
     *                     @OA\Property(property="owner_order", type="string", example="Hasan Zaeter"),
     *                     @OA\Property(property="total_price", type="string", example="14000.00"),
     *                     @OA\Property(property="number_of_products", type="intger", example="2"),
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
     *         description="Store not found",
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
     *                 example="No store found"
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
    public function getOrdersForStore(Request $request)
    {
        if (! $store = auth()->user()->store) {
            return JsonResponseHelper::errorResponse(__('messages.no_store'), [], 404);
        }

        $orders = $this->dashboardService->getAllOrdersForStore($store->id);

        return JsonResponseHelper::successResponse(__('messages.orders_fetched'), $orders);
    }

    /**
     * @OA\put(
     *     path="/dashboard/orders/update",
     *     summary="Update the status of an order",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"order_id", "status"},
     *
     *                 @OA\Property(property="order_id", type="integer", example=2, description="The ID of the order"),
     *                 @OA\Property(property="status", type="string", example="approved", description="The new status of the order", enum={"pending", "approved", "rejected", "delivered"})
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order updated successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200)
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
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="data", type="object", example={"order_id": "The order_id field is required."}),
     *             @OA\Property(property="status_code", type="integer", example=400)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function updateOrderStatus(UpdateOrderStatusRequest $request)
    {
        $validated = $request->validated();

        $order = $this->dashboardService->updateOrder($validated['order_id'], $validated['status']);

        if (! $order) {
            return JsonResponseHelper::errorResponse(__('messages.order_not_found'), [], 404);
        }

        return JsonResponseHelper::successResponse(__('messages.order_updated', [], 200));
    }
}
