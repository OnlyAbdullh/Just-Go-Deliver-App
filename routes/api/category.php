<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['localization'])->group(function () {
    Route::get('category', [CategoryController::class, 'index']);

    Route::middleware('auth.jwt')->group(function () {
        Route::post('category', [CategoryController::class, 'store']);
        Route::put('category/{id}', [CategoryController::class, 'update']);
        Route::delete('category/{id}', [CategoryController::class, 'destory']);
    });
});
