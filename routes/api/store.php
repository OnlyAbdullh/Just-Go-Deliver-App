<?php

use App\Helper\JsonResponseHelper;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('stores',[StoreController::class,'index']);
Route::post('stores',[StoreController::class,'store']);

Route::post('stores/{store}', [StoreController::class, 'update'])->missing(function () {
    return JsonResponseHelper::errorResponse('store not found', [], 404);
});

Route::delete('stores/{store}', [StoreController::class, 'destroy'])->missing(function () {
    return JsonResponseHelper::errorResponse('store not found', [], 404);
});;

Route::get('stores/{store}', [StoreController::class, 'show'])->missing(function () {
    return JsonResponseHelper::errorResponse('store not found', [], 404);
});

