<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;


use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Helpers\JsonResponseHelper;

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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     description="Array of products with store and quantity details",
     *                     @OA\Items(
     *                         type="object",
     *                         properties={
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
     *     @OA\Response(
     *         response=200,
     *         description="Orders created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Orders created successfully."
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     properties={
     *                         @OA\Property(
     *                             property="order_count",
     *                             type="integer",
     *                             description="Number of orders created",
     *                             example=2
     *                         )
     *                     }
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Failed to create orders."
     *                 )
     *             }
     *         )
     *     )
     * )
     */

    public function createOrders(Request $request)
    {
        $result = $this->orderService->createOrders($request->input('data'));

        return JsonResponseHelper::successResponse('Orders created successfully.', $result);
    }
}

