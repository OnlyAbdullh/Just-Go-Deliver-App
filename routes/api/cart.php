<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['auth.jwt', 'blacklist', 'localization']], function () {
    Route::post('carts/{store}/products/{product}/add', [CartController::class, 'add']);
    Route::get('carts/products', [CartController::class, 'getCartProducts']);
    Route::delete('/carts/delete-all', [CartController::class, 'deleteAll']);
    Route::put('carts/update-quantities', [CartController::class, 'updateQuantities']);
    Route::delete('carts/delete-products', [CartController::class, 'DeleteProducts']);

});
Route::middleware('guest.auth')->get('carts/getSize', [CartController::class, 'getCartSize']);
