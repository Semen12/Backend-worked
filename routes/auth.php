<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->group(function () {

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register.sanctum');

    /* Route::post('login', [AuthenticatedSessionController::class, 'store'])
               ->name('login.sanctum'); */ // используется вход fortify

    Route::post('password-forgot', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1') 
        ->name('password.email');

    Route::post('password-reset', [NewPasswordController::class, 'store'])
        ->name('password.reset');

});
