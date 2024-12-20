<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\Store;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * @OA\Post(
     *     path="/carts/{store_id}/products/{product_id}/add",
     *     summary="Add a product to the cart",
     *     tags={"Cart"},
     *     description="Adds a product to the authenticated user's cart. Validates stock availability before adding.",
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="store_id",
     *         in="path",
     *         description="The ID of the store where the product is located",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="The ID of the product to be added to the cart",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="quantity", type="integer", example=2, description="The quantity of the product to add to the cart")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product successfully added to the cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product added to cart successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error adding product to cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Not enough stock available")
     *         )
     *     ),
     * )
     */


    public function add(int $store_id, int $product_id, Request $request)
    {

        $result = $this->cartService->addProductToCart($store_id, $product_id, $request->input('quantity'));

        if (!$result['success']) {
            return JsonResponseHelper::errorResponse('', $result['message']);
        }
        return JsonResponseHelper::successResponse('', $result['message']);
    }

    /**
     * @OA\Get(
     *     path="/carts/products",
     *     summary="Retrieve all products in the user's cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     description="Fetch all products in the authenticated user's cart, including store and product details. Returns an empty array if the cart is empty.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of cart products",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true, description="Indicates if the operation was successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="List of products in the cart",
     *                 @OA\Items(
     *                     @OA\Property(property="store_id", type="integer", example=1, description="ID of the store"),
     *                     @OA\Property(property="store_name", type="string", example="Ali", description="Name of the store"),
     *                     @OA\Property(property="order_quantity", type="integer", example=2, description="Quantity ordered"),
     *                     @OA\Property(property="store_product_id", type="integer", example=1, description="ID of the product in the store"),
     *                     @OA\Property(property="price", type="string", example="400.00", description="Price of the product"),
     *                     @OA\Property(property="quantity", type="integer", example=3, description="Available quantity of the product"),
     *                     @OA\Property(property="description", type="string", example="Black", description="Description of the product"),
     *                     @OA\Property(property="product_id", type="integer", example=1, description="ID of the product"),
     *                     @OA\Property(property="product_name", type="string", example="iPhone", description="Name of the product"),
     *                     @OA\Property(
     *                         property="main_image",
     *                         type="string",
     *                         example="http://127.0.0.1:8000/storage/products/5xHVC1FalLQdtMDWEnHFwyABX0OF9zo6hFd6kytS.png",
     *                         description="URL of the main product image"
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="status_code", type="integer", example=200, description="HTTP status code of the response"),
     *             @OA\Property(property="total_price", type="integer", example=2750)
     *         )
     *     ),
     * )
     */
    public function getCartProducts()
    {
        $user = Auth::user();

        $result = $this->cartService->getAllProductsInCart($user);

        if (isset($result['message'])) {
            return JsonResponseHelper::successResponse($result['message']);
        } else {
            return JsonResponseHelper::successResponse('', $result['data']);
        }
    }

    public function updateQuantities(Request $request)
    {
        $user = Auth::user();
        $response = $this->cartService->updateCartQuantities($user->cart->id, $request->input('data'));
        return JsonResponseHelper::successResponse('', $response);
    }

    public function deleteAll()
    {
        $user = Auth::user();
        /* if (!$user->cart) {
            return JsonResponseHelper::successResponse('Your Cart is already empty');
        }*/
        DB::transaction(function () use ($user) {
            $user->cart->cartProducts()->delete();
        });
        return JsonResponseHelper::successResponse('All products have been deleted from the cart.');
    }
}
