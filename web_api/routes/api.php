<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;

Route::group([
    'prefix' => 'auth'
], function ($router) {
    //đăng nhập
    Route::post('login', [AuthController::class, 'login']);
    //đăng xuất
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    //lấy thông tin người dùng
    Route::get('profile', [AuthController::class, 'profile'])->middleware('auth:api');
    //đăng kí người dùng (user)
    Route::post('register', [AuthController::class, 'registerUser']);
    //đăng kí admin
    Route::post('register-admin', [AuthController::class, 'registerAdmin'])->middleware('auth:api');
});


Route::group([
    'prefix' => 'change-password'
], function () {
    // API gửi OTP
    Route::post('send-otp', [EmailController::class, 'sendOtp']);
    Route::post('verify-otp', [EmailController::class, 'verifyOtp']);
    Route::patch('reset-password', [AuthController::class, 'resetPassword']);
});

