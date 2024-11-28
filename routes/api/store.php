<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;

Route::get('stores',[StoreController::class,'index']);
Route::post('stores',[StoreController::class,'store']);
