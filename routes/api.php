<?php

/*@formatter:on*/

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CustomTwoFactorAuthenticationController;
use App\Http\Controllers\MasterPasswordController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => $user->email_verified_at ? true : false,
                'master_password' => $user->master_password ? true : false,
                'two_factor' => $user->hasEnabledTwoFactorAuthentication(),
            ],

        ]);
    });

    Route::patch('/user/update-name', [ProfileController::class, 'updateName'])
        ->name('profile.name');

    Route::patch('/user/update-email-unverified', [ProfileController::class, 'updateEmailUnverified'])
        ->name('profile.email.unverified');

    Route::middleware('verified')->group(function () {

        Route::post('/user/update-email-send', [ProfileController::class, 'sendCodeEmails'])
            ->name('profile.send.сode.emails');

        Route::patch('/user/update-email-verified', [ProfileController::class, 'updateEmailVerified'])
            ->name('profile.email.verified');

        Route::post('/user/create-master-password', [MasterPasswordController::class, 'store'])
            ->name('master.password.create');

        Route::middleware('verify.master.password')->group(function () {

            Route::patch('/user/update-master-password', [MasterPasswordController::class, 'update'])
                ->name('master.password.update');

            Route::post('/user/link-master-password', [MasterPasswordController::class, 'resetLink'])
                ->name('sendResetLinkForMasterPassword');

            Route::post('/user/reset-master-password', [MasterPasswordController::class, 'reset'])
                ->middleware('signed:relative')
                ->name('reset.master.password');

            Route::post('/accounts/create', [AccountController::class, 'store'])
                ->name('account.create');

            Route::get('/accounts/types', [AccountController::class, 'indexAccountTypes'])
                ->name('accounts.types');

            Route::get('/accounts', [AccountController::class, 'index'])
                ->name('account.index');

            Route::post('/set-master-password', [MasterPasswordController::class, 'setMasterPassword'])
                ->name('set.master.password');

            Route::get('/check-master-password', [MasterPasswordController::class, 'checkMasterPassword'])
                ->name('check.master.password');

            // группа для middleware master-password

            Route::middleware('master.password.check')->group(function () {

                Route::get('/accounts/{id}', [AccountController::class, 'show'])
                    ->whereNumber('id') // Ограничение параметра id, чтобы он содержал только цифры
                    ->name('account.show');

                Route::delete('/accounts/destroy/{id}', [AccountController::class, 'destroy'])
                    ->whereNumber('id') // Ограничение параметра id, чтобы он содержал только цифры
                    ->name('account.destroy');

                Route::put('/accounts/update/{id}', [AccountController::class, 'update'])
                    ->whereNumber('id') // Ограничение параметра id, чтобы он содержал только цифры
                    ->name('account.update');
            });
        });
    });
    Route::patch('/user/password-update', [PasswordController::class, 'update'])
        ->name('password.update');

    Route::post('/user/send-delete-code', [ProfileController::class, 'sendConfirmationCode'])
        ->middleware('verified')
        ->name('profile.send.delete.code');

    Route::delete('/user/destroy', [ProfileController::class, 'destroy'])
        ->name('profile.delete');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:60,1')
        ->name('verification.send');
    //ограничение по времени 60 попыток в минуту ( по умолчанию было 6,1)
    Route::get('/email-verify', VerifyEmailController::class)
        ->middleware(['signed:relative', 'throttle:60,1'])  // добавлен защитник: подписанный адрес с относительной ссылкой (т.е. без домена)
        ->name('verification.verify');

    Route::group(['middleware' => 'verified'], function () {
       
        Route::post('/user/two-factor/send-enable-code', [CustomTwoFactorAuthenticationController::class, 'sendVerificationEnabledCode'])
            ->name('two-factor.send-enable-code');

        // Маршрут для включения двухфакторной аутентификации, переопределён
        Route::post('/user/two-factor-authentication', [CustomTwoFactorAuthenticationController::class, 'store'])
            ->name('two-factor.store');

        // Маршрут для отправки кода подтверждения на почту для отключения 2FA
        Route::post('user/two-factor/send-confirmation-code', [CustomTwoFactorAuthenticationController::class, 'sendConfirmationCode'])
            ->name('two-factor.send-confirmation-code');

        // Маршрут для отключения двухфакторной аутентификации, переопределён

        Route::delete('user/two-factor-authentication', [CustomTwoFactorAuthenticationController::class, 'destroy']) // немного переделать логику
            ->name('two-factor.disable');
    });


    /*    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                 ->name('logout.sanctum'); */ // выход с помощью fortify */

});
