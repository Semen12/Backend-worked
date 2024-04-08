<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\PasswordController;

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1') 
                ->name('verification.send'); 

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']) 
                ->name('logout');
                
    Route::patch('/user/update-profile',[ProfileController::class,'update'])
                ->name('profile.update');

     Route::put('/user/password-update', [PasswordController::class, 'update'])->name('password.update');
         
    
     Route::delete('/user/destroy', [ProfileController::class, 'destroy'])
                ->name('profile.destroy');
    //

});
              
