<?php

use App\Http\Controllers\OTPController;
use App\Http\Controllers\UserController;
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


Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('refresh', [UserController::class, 'refresh']);

Route::post('send-otp', [OTPController::class, 'sendOTP']);
Route::post('validate-otp', [OTPController::class, 'validateOTP']);

Route::group(['middleware' => ['auth.jwt', 'blacklist']], function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('protected-resource', function () {
        return response()->json([
            'message' => 'You have accessed a protected resource!'
        ]);
    });
});

