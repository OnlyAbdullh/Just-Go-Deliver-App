<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\JsonResponseHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\ProductController;
use App\Models\Product;

Route::middleware(['guest.auth', 'localization'])->group(function () {
    Route::get('stores/{store}/{product}/show', [ProductController::class, 'show'])->name('products.show')
        ->missing(fn(Request $request) => handleMissingRoute($request));
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{name}', [ProductController::class, 'search']);
});

Route::middleware(['auth.jwt', 'localization', 'blacklist'])->group(function () {
    Route::post('stores/{store}', [ProductController::class, 'store'])
        ->missing(function () {
            app()->setLocale(request()->header('Accept-Language', 'en'));
            return JsonResponseHelper::errorResponse(('messages.storae_not_found'), [], 401);
        });

    Route::post('stores/{store}/{product}', [ProductController::class, 'update'])
        ->missing(fn(Request $request) => handleMissingRoute($request));

    Route::delete('stores/{store}/{product}/delete', [ProductController::class, 'destroy'])
        ->missing(fn(Request $request) => handleMissingRoute($request));
});


function handleMissingRoute(Request $request)
{
    app()->setLocale(request()->header('Accept-Language', 'en'));
    $message = collect([
        ('messages.store_not_found'),
        ('messages.product_not_found_in_store')
    ])->filter()->join( ('messages.or'));

    return JsonResponseHelper::errorResponse($message, [], 404);
}
