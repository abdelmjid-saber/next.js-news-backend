<?php

use App\Http\Controllers\Auth\JwtAuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SendEmailVerificationNotificationController;
use App\Http\Controllers\Auth\SendPasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [JwtAuthController::class, 'register']);

Route::post('/login', [JwtAuthController::class, 'login']);

Route::post('/refresh', [JwtAuthController::class, 'refresh']);

Route::post('/forgot-password', SendPasswordResetLinkController::class);

Route::post('/reset-password', ResetPasswordController::class);

Route::middleware('auth:api')->group(function () {
    Route::post('/email/verification-notification', SendEmailVerificationNotificationController::class)->middleware(['throttle:6,1']);
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1']);
    Route::post('/logout', [JwtAuthController::class, 'logout']);
});
