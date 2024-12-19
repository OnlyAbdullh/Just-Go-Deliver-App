<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\Store;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    /**
     * @OA\Post(
     *     path="/carts/{store}/products/{product}/add",
     *     summary="Add a product to the cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         description="Store ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="quantity", type="integer", description="Quantity of the product to add")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to cart successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */

    public function add(int $store_id, int $product_id, Request $request)
    {

        $result = $this->cartService->addProductToCart($store_id, $product_id, $request->input('quantity'));

        if (!$result['success']) {
            return JsonResponseHelper::errorResponse([$result['message']]);
        }
        return JsonResponseHelper::successResponse([$result['message']]);
    }
    /**
     * @OA\Get(
     *     path="/carts/products",
     *     summary="Retrieve all products in the user's cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of cart products or empty cart message",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", description="Product ID"),
     *                     @OA\Property(property="name", type="string", description="Product name"),
     *                     @OA\Property(property="quantity", type="integer", description="Quantity in the cart"),
     *                     @OA\Property(property="price", type="number", format="float", description="Product price")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getCartProducts()
    {
        $user = Auth::user();

        $result = $this->cartService->getAllProductsInCart($user);

        if ($result['message']) {
            return JsonResponseHelper::successResponse($result['message']);
        } else {
            return JsonResponseHelper::successResponse('', $result['data']);
        }
    }

}
