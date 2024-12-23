<?php

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::middleware('localization')->group(function () {

    Route::middleware('auth.jwt')->group(function () {
        Route::post('stores', [StoreController::class, 'store'])->name('stores.store');

        Route::post('stores/{store}', [StoreController::class, 'update'])
            ->missing(function () {
                app()->setLocale(request()->header('Accept-Language', 'en'));

                return JsonResponseHelper::errorResponse(__('messages.store_not_found'), [], 401);
            });

        Route::delete('stores/{store}', [StoreController::class, 'destroy'])
            ->missing(function () {
                app()->setLocale(request()->header('Accept-Language', 'en'));

                return JsonResponseHelper::errorResponse(__('messages.store_not_found'), [], 401);
            });
        //   Route::post('stores/{storeId}/products', [ProductController::class, 'store']);
    });

    Route::get('stores', [StoreController::class, 'index'])->name('stores.index');
    Route::get('stores/{store}', [StoreController::class, 'show'])
        ->missing(function () {
            app()->setLocale(request()->header('Accept-Language', 'en'));

            return JsonResponseHelper::errorResponse(__('messages.store_not_found'), [], 401);
        });
});
