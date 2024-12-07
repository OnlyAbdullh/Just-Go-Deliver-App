<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('products', [ProductController::class, 'index']);

Route::middleware(['auth.jwt', 'localization'])->group(function () {
    Route::post('products/{storeId}', [ProductController::class, 'store']);
    Route::post('products/{storeId}/{productId}', [ProductController::class, 'update']);
});
