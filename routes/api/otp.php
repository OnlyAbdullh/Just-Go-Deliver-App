<?php
use App\Http\Controllers\OTPController;
use Illuminate\Support\Facades\Route;

Route::post('resend-otp', [OTPController::class, 'ResendOTP']);
Route::post('validate-otp', [OTPController::class, 'validateOTP']);
