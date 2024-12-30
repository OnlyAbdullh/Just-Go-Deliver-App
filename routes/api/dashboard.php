<?php

use App\Http\Controllers\DashBoardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.jwt','blacklist','localization'])->group(function(){
    Route::get('dashboard/{user}',[DashBoardController::class, 'getProducts']);
});