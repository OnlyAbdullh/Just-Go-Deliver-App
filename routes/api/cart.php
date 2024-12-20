<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['auth.jwt', 'blacklist']], function () {
    Route::post('carts/{store}/products/{product}/add', [CartController::class, 'add']);
    Route::get('carts/products', [CartController::class, 'getCartProducts']);
    Route::delete('carts/deleteAll', [CartController::class, 'deleteAll']);
    Route::put('carts/update', [CartController::class, 'updateQuantities']);
});
