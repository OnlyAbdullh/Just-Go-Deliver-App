<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.jwt','localization'])->prefix('users')->group(function () {
    Route::post('/assign-role', [RoleController::class, 'store']);
    Route::post('/revoke-role', [RoleController::class, 'delete']);
});


