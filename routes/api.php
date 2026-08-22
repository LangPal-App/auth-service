<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function() {
    Route::get('/profile', [ProfileController::class, 'show'])->name('getProfile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('updateProfile');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('updatePassword');
    Route::patch('/profile/image', [ProfileController::class, 'uploadProfileImage'])->name('uploadProfileImage');
});
