<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['auth.jwt', 'blacklist']], function () {
    Route::post('carts/{store}/products/{product}/add', [CartController::class,'add']);
});
