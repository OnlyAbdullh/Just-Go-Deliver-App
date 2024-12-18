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

    public function add(int $store_id, int $product_id, Request $request)
    {

        $result = $this->cartService->addProductToCart($store_id, $product_id, $request->input('quantity'));

        if (!$result['success']) {
            return JsonResponseHelper::errorResponse([$result['message']]);
        }
        return JsonResponseHelper::successResponse([$result['message']]);
    }

    public function getCartProducts()
    {
        $user = Auth::user();

        $result = $this->cartService->getAllProductsInCart($user);

        if (!$result['success']) {
            return JsonResponseHelper::errorResponse([$result['message']]);
        }

        return JsonResponseHelper::successResponse([$result['data']]);
    }

}
