<?php

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.jwt', 'localization'])->group(function () {
    Route::post('images/stores/{store}/products/{product}', [ImageController::class, 'store'])
        ->missing(fn (Request $request) => handleMissingRoute($request));
    Route::post('images/{image}', [ImageController::class, 'update'])->missing(function () {
        app()->setLocale(request()->header('Accept-Language', 'en'));
        return JsonResponseHelper::errorResponse('image not found', [], 404);
    });
    Route::delete('images/{image}', [ImageController::class, 'destroy'])->missing(function () {
        app()->setLocale(request()->header('Accept-Language', 'en'));

        return JsonResponseHelper::errorResponse('image not found', [], 404);
    });
});
