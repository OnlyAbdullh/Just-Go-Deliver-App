<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\Product;
use App\Models\Store;
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
     *     path="/api/carts/{store}/{product}",
     *     summary="Add a product to the cart",
     *     tags={"Cart"},
     *     description="Adds a product to the authenticated user's cart. Validates stock availability before adding.",
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *
     *     @OA\Parameter(
     *         name="store_id",
     *         in="path",
     *         description="The ID of the store where the product is located",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="The ID of the product to be added to the cart",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="quantity", type="integer", example=2, description="The quantity of the product to add to the cart")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product successfully added to the cart",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product added to cart successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Error adding product to cart",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Not enough stock available")
     *         )
     *     ),
     * )
     */
    public function add(int $store_id, int $product_id, Request $request)
    {

        $result = $this->cartService->addProductToCart($store_id, $product_id, $request->input('quantity'));

        if (! $result['success']) {
            return JsonResponseHelper::errorResponse('', $result['message']);
        }

        return JsonResponseHelper::successResponse('', $result['message']);
    }

    /**
     * @OA\Get(
     *     path="/api/carts",
     *     summary="Retrieve all products in the user's cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     description="Fetch all products in the authenticated user's cart, including store and product details. Returns an empty array if the cart is empty.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of cart products",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true, description="Indicates if the operation was successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="List of products in the cart",
     *
     *                 @OA\Items(
     *
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
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         example="The message is one of these: No Products available for now. OR only 3 are available for now. OR available now.",
     *                         description="Cart quantity for this product"
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="status_code", type="integer", example=200, description="HTTP status code of the response"),
     *             @OA\Property(property="total_price", type="integer", example=2750, description="Total price of all products in the cart")
     *         )
     *     )
     * )
     */
    public function getCartProducts(bool $onlyUnavailable)
    {
        $user = Auth::user();

        $result = $this->cartService->getAllProductsInCart($user,$onlyUnavailable);

        if (isset($result['message'])) {
            return JsonResponseHelper::successResponse($result['message']);
        } else {
            return JsonResponseHelper::successResponse('', $result['data']);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/carts/update-quantities",
     *     summary="Update quantities of products in the cart",
     *     description="Update the quantities of items in the user's cart based on stock availability. Updates only items with valid quantities.",
     *     tags={"Cart"},
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
     *                     description="Array of items to update",
     *
     *                     @OA\Items(
     *                         type="object",
     *                         properties={
     *
     *                             @OA\Property(property="product_id", type="integer", description="ID of the product", example=101),
     *                             @OA\Property(property="store_id", type="integer", description="ID of the store", example=1),
     *                             @OA\Property(property="cart_amount", type="integer", description="Updated cart quantity", example=5)
     *                         }
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success responses with multiple possibilities",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *                 oneOf={
     *
     *                     @OA\Schema(
     *                         type="object",
     *                         properties={
     *
     *                             @OA\Property(property="message", type="string", example="Updated Product ID 101 to quantity 5."),
     *                             @OA\Property(property="store_id", type="integer", description="ID of the store", example=1),
     *                             @OA\Property(property="product_id", type="integer", example=101),
     *                             @OA\Property(property="cart_amount", type="integer", example=5)
     *                         }
     *                     ),
     *
     *                     @OA\Schema(
     *                         type="object",
     *                         properties={
     *
     *                             @OA\Property(property="message", type="string", example="Only 3 of Product ID 101 is available. Updated the quantity to 3."),
     *                             @OA\Property(property="store_id", type="integer", description="ID of the store", example=1),
     *                             @OA\Property(property="product_id", type="integer", example=101),
     *                             @OA\Property(property="cart_amount", type="integer", example=3)
     *                         }
     *                     ),
     *
     *                     @OA\Schema(
     *                         type="object",
     *                         properties={
     *
     *                             @OA\Property(property="message", type="string", example="There is no product available for now for Product ID 3."),
     *                             @OA\Property(property="store_id", type="integer", description="ID of the store", example=1),
     *                             @OA\Property(property="product_id", type="integer", example=3),
     *                             @OA\Property(property="cart_amount", type="integer", example=0)
     *                         }
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *
     *                 @OA\Property(property="message", type="string", description="Error message describing the issue", example="Invalid input data. Please ensure all items have valid store_id, product_id, and quantity.")
     *             }
     *         )
     *     )
     * )
     */
    public function updateQuantities(Request $request)
    {
        $user = Auth::user();
        $response = $this->cartService->updateCartQuantities($user->cart->id, $request->input('data'));

        return JsonResponseHelper::successResponse('', $response);
    }

    /**
     * @OA\Delete (
     *     path="/api/carts/delete-products",
     *     summary="Delete specific products from the cart",
     *     description="Deletes specific products from the user's cart based on the provided product and store IDs.",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *
     *          @OA\Parameter(
     *          name="Accept-Language",
     *          in="header",
     *          description="The language to return results in (ar for Arabic, en for English)",
     *          required=false,
     *
     *          @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *      ),
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
     *                     description="Array of items to delete",
     *
     *                     @OA\Items(
     *                         type="object",
     *                         properties={
     *
     *                             @OA\Property(property="product_id", type="integer", description="ID of the product to delete", example=101),
     *                             @OA\Property(property="store_id", type="integer", description="ID of the store where the product is located", example=1)
     *                         }
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Products deleted successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *
     *                 @OA\Property(property="message", type="string", description="Success message", example="3 products were Deleted from the Cart successfully")
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *
     *                 @OA\Property(property="message", type="string", description="Error message describing the issue", example="Invalid data provided. Please check the input and try again.")
     *             }
     *         )
     *     ),
     * )
     */
    public function DeleteProducts(Request $request)
    {
        $user = Auth::user();
        $RowsDeleted = $this->cartService->DeleteCartProducts($user->cart, $request->input('data'));

        return JsonResponseHelper::successResponse(__('messages.cart_products_deleted_success', ['count' => $RowsDeleted]));
    }

    /**
     * @OA\Delete(
     *     path="/api/carts/delete-all",
     *     summary="Delete all products from the cart",
     *     description="Deletes all products from the user's cart.",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *
     *          @OA\Parameter(
     *          name="Accept-Language",
     *          in="header",
     *          description="The language to return results in (ar for Arabic, en for English)",
     *          required=false,
     *
     *          @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *      ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="All products deleted successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *
     *                 @OA\Property(property="message", type="string", description="Success message", example="All products have been deleted from the cart.")
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *
     *                 @OA\Property(property="message", type="string", description="Error message describing the issue", example="Failed to delete products. Please try again.")
     *             }
     *         )
     *     ),
     * )
     */
    public function deleteAll()
    {
        $this->cartService->deleteAll();
        return JsonResponseHelper::successResponse(__('messages.cart_products_deleted'));
    }

    /**
     * @OA\Get(
     *     path="/api/carts/getSize",
     *     summary="Get the number of products in the cart",
     *     tags={"Cart"},
     *     description="Returns the total number of products in the user's cart. If the user is not authenticated or the cart does not exist, it returns 0.",
     *     security={
     *         {"BearerAuth": {}}
     *     },
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cart size retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="data", type="integer", example=3)
     *         )
     *     ),
     * )
     */
    public function getCartSize()
    {
        $user = Auth::user();

        return JsonResponseHelper::successResponse('Cart size retrieved successfully', $user?->cart?->cart_count ?? 0);
    }
}
