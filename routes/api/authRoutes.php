<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
   Route::post('register', [AuthController::class, 'register']);
   Route::post('verify-email', [AuthController::class, 'verifyEmail']);
   Route::post('resend-verification-code', [AuthController::class, 'resendVerificationCode']);
   Route::post('forget-password', [AuthController::class, 'forgetPassword']);
   Route::post('verify-reset-pin', [AuthController::class, 'verifyResetPin']);
   Route::post('resend-forget-password', [AuthController::class, 'resendForgetPassword']);
   Route::post('reset-password', [AuthController::class, 'resetPassword']);
   Route::post('login', [AuthController::class, 'login']);
   Route::post('admin-login', [AuthController::class, 'loginAdmin']);
   Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:api')->post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/refresh-token', [AuthController::class, 'refreshToken']);
Route::middleware('auth:api')->get('auth/me', [AuthController::class, 'me']);
