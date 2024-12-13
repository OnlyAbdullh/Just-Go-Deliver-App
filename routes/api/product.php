<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\JsonResponseHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\ProductController;
use App\Models\Product;

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/{store}/{product}', [ProductController::class, 'show'])->name('products.show')
    ->missing(fn(Request $request) => handleMissingRoute($request));


Route::middleware(['auth.jwt', 'localization', 'blacklist'])->group(function () {
    Route::post('products/{store}', [ProductController::class, 'store'])
        ->missing(function () {
            app()->setLocale(request()->header('Accept-Language', 'en'));
            return JsonResponseHelper::errorResponse(__('messages.store_not_found'), [], 401);
        });

    Route::post('stores/{store}/products/{product}', [ProductController::class, 'update'])
        ->missing(fn(Request $request) => handleMissingRoute($request));

    Route::delete('stores/{store}/products/{product}', [ProductController::class, 'destroy'])
        ->missing(fn(Request $request) => handleMissingRoute($request));
});


function handleMissingRoute(Request $request)
{
    $message = collect([
        !$request->route('store') ? 'Store not found ' : null,
        !$request->route('product') ? 'Product not found' : null,
    ])->filter()->join(' and ');

    return JsonResponseHelper::errorResponse($message, [], 404);
}
