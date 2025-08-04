<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {


      Route::controller(AuthController::class)->prefix('auth')->group(function () {

        Route::post('/login' , 'login')->name('auth.login')->middleware('throttle:10,1');
        Route::post('/register' , 'register')->name('auth.register')->middleware('throttle:5,1');
        Route::post('/logout' , 'logout')->name('auth.logout')->middleware('auth:sanctum');
        Route::post('/password/reset' , 'resetPassword')->name('auth.password.reset')->middleware('throttle:5,1');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/verification/resend', 'resendVerification')->name('auth.verification.resend')->middleware('auth:sanctum');
            Route::post('/verification/verify', 'verifyEmail')->name('auth.verification.verify')->middleware('auth:sanctum');
            Route::post('/password/reset/verify', 'verifyResetPassword')->name('auth.reset.verify')->middleware(['throttle:5,1', 'auth:sanctum']);
        });

      });


});
