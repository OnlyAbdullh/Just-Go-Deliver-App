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
     * @OA\Post(
     *     path="/api/favorites",
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
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product does not belong to the specified store.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product does not belong to the specified store."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Product is already in your favorites.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product is already in your favorites."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An unexpected error occurred.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function add(int $store_id, int $product_id): \Illuminate\Http\JsonResponse
    {
        $result = $this->favoriteService->addToFavorites($product_id, $store_id);

        if ($result === 'not_in_store') {
            return JsonResponseHelper::errorResponse('Product does not belong to the specified store.', [], 404);
        }

        if ($result === 'already_favorited') {
            return JsonResponseHelper::errorResponse('Product is already in your favorites.', [], 409);
        }

        if ($result === 'success') {
            return JsonResponseHelper::successResponse('Product added to favorites successfully.', [], 201);
        }

        return JsonResponseHelper::errorResponse('An unexpected error occurred.', [], 500);
    }

}
