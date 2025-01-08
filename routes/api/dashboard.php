<?php

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\DashBoardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.jwt', 'blacklist', 'localization'])->group(function () {
    Route::get('dashboard/orders', [DashBoardController::class, 'getOrdersForStore']);

    Route::get('dashboard/{user}', [DashBoardController::class, 'getProducts'])
        ->missing(function (Request $request) {
            app()->setLocale($request->header('Accept-Language', 'en'));

            return JsonResponseHelper::errorResponse(__('messages.user_not_found'), [], 404);
        });
    Route::get('dashboard/statistics/{user}', [DashBoardController::class, 'getProductStatistics'])
        ->missing(function (Request $request) {
            app()->setLocale($request->header('Accept-Language', 'en'));

            return JsonResponseHelper::errorResponse(__('messages.user_not_found'), [], 404);
        });
    Route::put('dashboard/orders/update', [DashBoardController::class, 'updateOrderStatus']);
});
