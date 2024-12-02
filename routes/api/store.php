<?php

use App\Helper\JsonResponseHelper;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::middleware('localization')->group(function () {

    Route::get('stores', [StoreController::class, 'index'])->name('stores.index');
    Route::post('stores', [StoreController::class, 'store'])->name('stores.store');

    Route::post('stores/{store}', [StoreController::class, 'update']);

    Route::delete('stores/{store}', [StoreController::class, 'destroy']);

    Route::get('stores/{store}', [StoreController::class, 'show']);
});
