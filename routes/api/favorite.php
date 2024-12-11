<?php

use App\Http\Controllers\FavoriteController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth.jwt', 'blacklist']], function () {
    Route::get('/favorites/{store_id}/products/{product_id}', [FavoriteController::class, 'add']);
    Route::delete('/favorites/{store_id}/products/{product_id}', [FavoriteController::class, 'remove']);
    Route::get('/favorites/{store_id}/products/{product_id}/check', [FavoriteController::class, 'check']);
});
