<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorDisabledCode;
use App\Models\TwoFactorConfirmationDisabledCode;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Contracts\TwoFactorDisabledResponse;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;

class CustomTwoFactorAuthenticationController extends Controller
{
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required','min:8','max:255','current_password'],
            'confirmation_code' => ['required'],
        ]);

        $user = $request->user();

        // Проверка, включена ли у пользователя 2FA
        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return response()->json(['error' => 'Двухфакторная аутентификация не включена'], 422);
        }



        // Проверка кода подтверждения
        $confirmationCode = TwoFactorConfirmationDisabledCode::where('user_id', $user->id)
            ->first();

            if(is_null($confirmationCode)){
                return response()->json(['error' => 'Код подтверждения не найден'], 422);
            }
        if (! Hash::check($request->confirmation_code, $confirmationCode->code) || $confirmationCode->isExpired()) {
            return response()->json(['error' => 'Недействительный или истекший код подтверждения'], 422);
        }

        // Удаление кода подтверждения после успешной проверки
        $confirmationCode->delete();

        // Отключение 2FA
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        TwoFactorAuthenticationDisabled::dispatch($user); //событие отключения 2FA

        return response()->json(['message' => 'Двухфакторная аутентификация успешно отключена'], 200);

    }

    public function sendConfirmationCode(Request $request)
    {
        $user = $request->user();
        $confirmationCode = rand(100000, 999999); // Генерация кода
        $expiresAt = Carbon::now()->addMinutes(10); // Время действия кода

        // Сохранение кода в базу данных

        TwoFactorConfirmationDisabledCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $confirmationCode, 'expires_at' => $expiresAt]
        );


        // Отправка кода на почту
        Mail::to($user->email)->send(new TwoFactorDisabledCode($confirmationCode));

        return response()->json(['message' => 'Код подтверждения отправлен на вашу почту.'], 200);
    }
}
