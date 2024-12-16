<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::post('carts/{store}/products/{product}/add', [CartController::class]);
