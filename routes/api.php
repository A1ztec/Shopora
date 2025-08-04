<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\SocialAuthController;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::controller(AuthController::class)->group(function () {
            Route::post('/login', 'login')->name('auth.login')->middleware('throttle:10,1');
            Route::post('/register', 'register')->name('auth.register')->middleware('throttle:5,1');

            Route::post('/verification/resend', 'resendVerificationCode')->name('auth.verification.resend')->middleware('throttle:5,1');
            Route::post('/verification/verify', 'verifyEmail')->name('auth.verification.verify')->middleware('throttle:5,1');
            Route::post('/password/reset/send', 'sendResetPasswordCode')->name('auth.password.reset.send')->middleware('throttle:5,1');
            Route::post('/password/reset', 'resetPassword')->name('auth.password.reset')->middleware('throttle:5,1');

            Route::middleware('auth:sanctum')->group(function () {
                Route::post('/logout', 'logout')->name('auth.logout');
            });
        });

        Route::prefix('social')->controller(SocialAuthController::class)->group(function () {
            Route::post('/{provider}', 'socialAuth')
                ->name('auth.social.auth');
        });
    });
});
