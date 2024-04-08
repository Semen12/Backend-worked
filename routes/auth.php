<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordResetLinkController as PasswordResetLinkController1;
use App\Http\Controllers\ProfileController;

Route::middleware(['guest'])->group(function () {
    Route::post('register', [RegisteredUserController::class, 'store'])
                ->name('register');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
                ->name('login');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');

    
});


