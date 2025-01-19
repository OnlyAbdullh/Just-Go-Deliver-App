<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Artisan;
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

Route::middleware('localization')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('dashboard/login/{role_needed}', [AuthController::class, 'dashboardLogin']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::group(['middleware' => ['auth.jwt', 'blacklist']], function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::get('/api/documentation', function () {
    Artisan::call('l5-swagger:generate');

    return redirect('/swagger-ui');
});
// ./vendor/bin/pint
