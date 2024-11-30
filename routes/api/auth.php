<?php

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


Route::post('register', [UserController::class, 'register']); // Register a new user
Route::post('login', [UserController::class, 'login']);       // Login and get tokens
Route::post('refresh', [UserController::class, 'refresh']);   // Refresh access token

Route::group(['middleware' => ['auth.jwt', 'blacklist']], function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('protected-resource', function () {
        return response()->json([
            'message' => 'You have accessed a protected resource!'
        ]);
    });
});

