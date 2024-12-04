<?php

use App\Http\Controllers\OTPController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);


Route::group(['middleware' => ['auth.jwt', 'blacklist']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
});
Route::get('/api/documentation', function () {
    \Illuminate\Support\Facades\Artisan::call('l5-swagger:generate');
    return redirect('/swagger-ui');
});
