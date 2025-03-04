<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Post(
     *     path="/api/orders/create",
     *     tags={"Order"},
     *     summary="Create new orders",
     *     description="Creates orders for grouped products from different stores. Requires authentication.",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     description="Array of products with store and quantity details",
     *
     *                     @OA\Items(
     *                         type="object",
     *                         properties={
     *
     *                             @OA\Property(
     *                                 property="store_product_id",
     *                                 type="integer",
     *                                 description="ID of the product in the store",
     *                                 example=1
     *                             ),
     *                             @OA\Property(
     *                                 property="store_id",
     *                                 type="integer",
     *                                 description="ID of the store",
     *                                 example=1
     *                             ),
     *                             @OA\Property(
     *                                 property="quantity",
     *                                 type="integer",
     *                                 description="Quantity of the product",
     *                                 example=5
     *                             )
     *                         },
     *                         required={"store_product_id", "store_id", "quantity"}
     *                     )
     *                 )
     *             },
     *             required={"data"}
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Orders created successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Orders created successfully."
     *                 ),
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Some items in your cart are out of stock",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *
     *                 @OA\Property(
     *                     property="state",
     *                     type="string",
     *                     example="false"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Order Failed, Some Items in Your Cart Are Out of Stock"
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - Quantity in the request is different from the quentity in the cart",
     *     )
     * )
     */
    public function createOrders(Request $request)
    {
        $result = $this->orderService->createOrders($request->input('data'));

        if (isset($result['state'])) {
            return JsonResponseHelper::errorResponse($result['message']);
        }

        return JsonResponseHelper::successResponse('Orders created successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get user orders",
     *     description="Retrieve all orders for the authenticated user, including details like order date, status, total price, and the number of products in each order.",
     *     tags={"Order"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of user orders retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *                 type="object",
     *
     * @OA\Property(
     *       property="id",
     *       type="integer",
     *      description="Order ID",
     *       example=10
     *  ),
     *   @OA\Property(
     *      property="order_date",
     *       type="string",
     *      format="date",
     *   description="Date of the order",
     *      example="2024-12-31"
     *  ),
     *   @OA\Property(
     *      property="status",
     *     type="string",
     *      description="Status of the order",
     *       example="pending"
     *   ),
     *   @OA\Property(
     *       property="total_price",
     *      type="number",
     *       format="float",
     *      description="Total price of the order",
     *       example=1525.50
     *   ),
     *   @OA\Property(
     *       property="order_reference",
     *       type="string",
     *       description="Unique order reference",
     *       example="ORD-12345-XYZ"
     *   ),
     *   @OA\Property(
     *       property="number_of_products",
     *       type="integer",
     *       description="Number of products in the order",
     *       example=7
     *   ),
     *   @OA\Property(
     *       property="image",
     *       type="string",
     *       format="url",
     *       description="Main image of the order's products",
     *       example="https://example.com/images/main-image.jpg"
     *   )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function getUserOrders()
    {
        $orders = $this->orderService->getUserOrders();

        return JsonResponseHelper::successResponse('', $orders);
    }

    /**
     * @OA\Delete(
     *     path="/orders/{orderId}",
     *     summary="Cancel an order",
     *     description="Allows the user to cancel a pending order. Only orders with a status of 'pending' can be cancelled and the products of the order will go again to the cart",
     *     operationId="cancelOrder",
     *     tags={"Order"},
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         description="The ID of the order to cancel",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="integer",
     *             example=123
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order cancelled successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order cancelled and deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Order not found or does not belong to the user",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found or does not belong to the user")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Order cannot be cancelled",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order cannot be cancelled")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function cancelOrder(int $orderId)
    {
        $response = $this->orderService->cancelOrder($orderId);

        if (! $response['success']) {
            return JsonResponseHelper::errorResponse($response['message'], [], $response['code'] ?? 400);
        }

        return JsonResponseHelper::successResponse($response['message']);
    }

    /**
     * @OA\Get(
     *     path="/orders/show/{orderId}",
     *     summary="Get details of an order with its products",
     *     description="Fetch a specific order by its ID along with its associated products and details.",
     *     operationId="showOrder",
     *     tags={"Order"},
     *
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         description="ID of the order to fetch",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with order and product details",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="successful",
     *                 type="string",
     *                 example="true"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="status", type="string", example="completed"),
     *                 @OA\Property(property="total_price", type="number", format="float", example=150.75),
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="store_id", type="integer", example=3),
     *                         @OA\Property(property="store_name", type="string", example="Tech Store"),
     *                         @OA\Property(property="product_id", type="integer", example=10),
     *                         @OA\Property(property="product_name", type="string", example="Smartphone"),
     *                         @OA\Property(property="category_id", type="integer", example=5),
     *                         @OA\Property(property="category_name", type="string", example="Electronics"),
     *                         @OA\Property(property="price", type="number", format="float", example=499.99),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="description", type="string", example="High-end smartphone with 128GB storage"),
     *                         @OA\Property(property="is_favorite", type="boolean", example=1),
     *                         @OA\Property(property="main_image", type="string", example="https://example.com/images/product.jpg"),
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     * @OA\Response(
     *         response=404,
     *         description="Order not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="successful",
     *                 type="string",
     *                 example="false"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Order not found"
     *             ),
     *         )
     *     )
     * )
     */
    public function showOrder(int $order_id)
    {
        $order = $this->orderService->getOrderWithProducts($order_id);

        if (! $order) {
            return JsonResponseHelper::errorResponse(__('messages.order_not_found'), [], 404);
        }

        // return $order;
        return JsonResponseHelper::successResponse('', new OrderResource($order));
    }
}
