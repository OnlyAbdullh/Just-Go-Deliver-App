<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Services\FavoriteService;

class FavoriteController extends Controller
{
    protected $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    /**
     * @OA\Get(
     *     path="/api//favorites/{store_id}/products/{product_id}",
     *     tags={"Favorites"},
     *     summary="Add a product to favorites",
     *     description="Adds a product to the user's favorites if it belongs to the specified store and is not already in favorites.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"store_id", "product_id"},
     *             @OA\Property(property="store_id", type="integer", example=1, description="ID of the store"),
     *             @OA\Property(property="product_id", type="integer", example=42, description="ID of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product added to favorites successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product added to favorites successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Product is already in your favorites.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product is already in your favorites."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An unexpected error occurred.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *         )
     *     )
     * )
     */
    public function add(int $store_id, int $product_id)
    {
        $result = $this->favoriteService->addToFavorites($product_id, $store_id);

        /* if ($result === 'not_in_store') {
             return JsonResponseHelper::errorResponse('Product does not belong to the specified store.', [], 404);
         }*/

        if ($result === 'already_favorite') {
            return JsonResponseHelper::errorResponse(__('messages.product_already_in_favorites'), [], 409);
        }

        if ($result === 'success') {
            return JsonResponseHelper::successResponse(__('messages.product_added_to_favorites'), [], 201);
        }

        return JsonResponseHelper::errorResponse(__('messages.unexpected_error'), [], 500);
    }

    /**
     * @OA\Delete(
     *     path="/favorites/{store_id}/products/{product_id}/remove",
     *     summary="Remove a product from favorites",
     *     tags={"Favorites"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"store_id", "product_id"},
     *             @OA\Property(property="store_id", type="integer", example=1, description="ID of the store"),
     *             @OA\Property(property="product_id", type="integer", example=101, description="ID of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product removed from favorites successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product removed from favorites successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product is not in your favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product is not in your favorites."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An unexpected error occurred",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *         )
     *     )
     * )
     */
    public function remove(int $store_id, int $product_id)
    {
        $result = $this->favoriteService->removeFromFavorites($product_id, $store_id);

        if ($result === 'not_in_favorites') {
            return JsonResponseHelper::errorResponse(__('messages.product_not_in_favorites'), [], 404);
        }
        if ($result === 'success') {
            return JsonResponseHelper::successResponse(__('messages.product_removed_from_favorites'), [], 200);
        }

        return JsonResponseHelper::errorResponse(__('messages.unexpected_error'), [], 500);
    }

    /**
     * @OA\Post (
     *     path="/api/favorites",
     *     tags={"Favorites"},
     *     summary="Get list of favorite products",
     *     description="Retrieves a list of the user's favorite products along with associated store details, such as store ID, store name, and product-specific details (price, quantity, description, sold quantity).",
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Favorites retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="successful",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Favorites retrieved successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="product_id", type="integer", example=4
     *                     ),
     *                     @OA\Property(
     *                         property="product_name",type="string",  example="laptop lenovo"
     *                     ),
     *                     @OA\Property(
     *                         property="category_id",type="integer", example=1
     *                     ),
     *                     @OA\Property(
     *                         property="store_id", type="integer",  example=3
     *                     ),
     *                     @OA\Property(
     *                         property="store_name", type="string", example="only one"
     *                     ),
     *                     @OA\Property(
     *                         property="price", type="number",  format="float",example=1000.00
     *                     ),
     *                     @OA\Property(
     *                         property="quantity", type="integer", example=4
     *                     ),
     *                     @OA\Property(
     *                         property="description",type="string", example="ram 16/ssd 512/Rtx 3050ti"
     *                     ),
     *                     @OA\Property(
     *                         property="sold_quantity", type="integer", example=0
     *                     ),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="successful", type="boolean",example=false
     *             ),
     *             @OA\Property(
     *                 property="message",type="string", example="Unauthenticated."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="successful",type="boolean",example=false
     *             ),
     *             @OA\Property(
     *                 property="message", type="string", example="An error occurred."
     *             )
     *         )
     *     )
     * )
     */
    public function list()
    {
        $favorites = $this->favoriteService->getFavoriteProducts();
        return JsonResponseHelper::successResponse(__('messages.favorites_retrieved'), $favorites);
    }

    /**
     * @OA\Get(
     *     path="/favorites/{store_id}/products/{product_id}/check",
     *     summary="Check if a product is favorited",
     *     tags={"Favorites"},
     *     @OA\Parameter(
     *         name="store_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the store"
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the product"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Favorite status retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Favorite status retrieved."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="is_favorite", type="boolean", example=true)
     *             )
     *         )
     *     )
     * )
     */
    public function check(int $store_id, int $product_id)
    {
        $isFavorite = $this->favoriteService->isProductFavorited($product_id, $store_id);
        return JsonResponseHelper::successResponse(__('messages.favorite_status_retrieved'), ['is_favorite' => $isFavorite]);
    }
}
