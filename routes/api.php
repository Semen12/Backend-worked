<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\PasswordController;

use App\Http\Controllers\MasterPassController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::patch('/user/update-name',[ProfileController::class,'updateName'])
                ->name('profile.name');

    Route::patch('/user/update-email-unverified',[ProfileController::class,'updateEmailUnverified'])
                ->name('profile.email.unverified');

        Route::middleware('verified')->group(function () {

                Route::post('/user/update-email-sent', [ProfileController::class, 'sendCodeEmails'])
                    ->name('profile.sentсode.emails');

                Route::patch('/user/update-email-verified', [ProfileController::class, 'updateEmailVerified'])
                    ->name('profile.email.verified');

                Route::post('/user/create-master-password', [MasterPassController::class, 'store'])
                    ->name('master.password.create');

                Route::patch('/user/update-master-password', [MasterPassController::class, 'update'])
                    ->name('master.password.update');

                Route::post('/user/link-master-password', [MasterPassController::class, 'resetLink'])
                    ->name('sendResetLinkForMasterPassword');

                Route::post('/user/reset-master-password', [MasterPassController::class, 'reset'])
                    ->middleware('signed:relative')
                    ->name('reset.master.password');

     });
    Route::patch('/user/password-update', [PasswordController::class, 'update'])->name('password.update');


    Route::delete('/user/destroy', [ProfileController::class, 'destroy'])
                ->name('profile.delete');

   Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:60,1')
                ->name('verification.send');
                 //ограничение по времени 60 попыток в минуту ( по умолчанию было 6,1)
    Route::get('/email-verify', VerifyEmailController::class)
                ->middleware(['signed:relative', 'throttle:60,1'])  // добавлен защитник: подписанный адрес с относительной ссылкой (т.е. без домена)
                ->name('verification.verify');

   /*    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout.sanctum'); */   // выход с помощью fortify */

});

