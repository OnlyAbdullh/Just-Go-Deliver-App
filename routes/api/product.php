<?php

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest.auth', 'localization'])->group(function () {
    Route::get('stores/{store}/products/{product}', [ProductController::class, 'show'])->name('products.show')
        ->missing(fn (Request $request) => handleMissingRoute($request));
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{name}', [ProductController::class, 'search']);
});

Route::middleware(['auth.jwt', 'localization', 'blacklist'])->group(function () {
    Route::post('stores/{store}/products', [ProductController::class, 'store'])
        ->missing(function () {
            app()->setLocale(request()->header('Accept-Language', 'en'));

            return JsonResponseHelper::errorResponse(__('messages.storae_not_found'), [], 401);
        });

    Route::post('stores/{store}/products/{product}', [ProductController::class, 'update'])
        ->missing(fn (Request $request) => handleMissingRoute($request));

    Route::delete('stores/{store}/products/{product}', [ProductController::class, 'destroy'])
        ->missing(fn (Request $request) => handleMissingRoute($request));
});

function handleMissingRoute(Request $request)
{
    app()->setLocale(request()->header('Accept-Language', 'en'));
    $message = collect([
        __('messages.store_not_found'),
        __('messages.product_not_found_in_store'),
    ])->filter()->join(__('messages.or'));

    return JsonResponseHelper::errorResponse($message, [], 404);
}
