<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth.jwt', 'blacklist', 'localization']], function () {

    Route::post('orders/create', [OrderController::class, 'createOrders']);
    Route::get('orders', [OrderController::class, 'getUserOrders']);
    Route::delete('orders/{order_id}', [OrderController::class, 'cancelOrder']);
    Route::get('/orders/show/{orderId}', [OrderController::class, 'showOrder']);
});
