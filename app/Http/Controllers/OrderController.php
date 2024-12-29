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
     * @OA\Post (
     *     path="/api/orders/create",
     *     tags={"Order"},
     *     summary="Create orders for grouped products",
     *     description="This endpoint allows users to create orders based on grouped products. Each product is associated with a store and includes details like quantity.",
     *     operationId="createOrders",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"data"},
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"store_product_id", "store_id", "quantity"},
     *                     @OA\Property(
     *                         property="store_product_id",
     *                         type="integer",
     *                         description="The ID of the store product"
     *                     ),
     *                     @OA\Property(
     *                         property="store_id",
     *                         type="integer",
     *                         description="The ID of the store"
     *                     ),
     *                     @OA\Property(
     *                         property="quantity",
     *                         type="integer",
     *                         minimum=1,
     *                         description="The quantity of the product to order"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Orders created successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="order_count",
     *                     type="integer",
     *                     description="The number of orders created",
     *                     example=2
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="An error occurred while creating orders."
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function createOrders(Request $request)
    {
        $result = $this->orderService->createOrders($request->input('data'));

        return JsonResponseHelper::successResponse('Orders created successfully.', $result);
    }
}

