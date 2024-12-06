<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::prefix('users')->group(function () {
    Route::get('', [UserController::class, 'index']);
    Route::get('{user}', [UserController::class, 'show']);
    Route::delete('{user}', [UserController::class, 'destroy']);
    Route::put('{user}', [UserController::class, 'update']);
    Route::post('{user}/upload', [UserController::class, 'storeImage']);
});
