<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Store $store, Product $product, Request $request)
    {
        $quantity = $request->input('quantity');

    }
}
