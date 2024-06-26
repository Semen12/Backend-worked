<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorDisabledCode;
use App\Mail\TwoFactorEnableCodeMail;
use App\Models\TwoFactorConfirmationDisabledCode;
use App\Models\TwoFactorEnableCode;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Contracts\TwoFactorDisabledResponse;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use App\Models\TwoFactorCode;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

class CustomTwoFactorAuthenticationController extends Controller
{
    protected $provider;

    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
    }
    public function sendVerificationEnabledCode(Request $request)
    {
        $user = $request->user();
        $confirmationCode = rand(100000, 999999); // Генерация кода
        $expiresAt = Carbon::now()->addMinutes(5); // Время действия кода

        // Сохранение кода в базу данных

        TwoFactorEnableCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $confirmationCode, 'expires_at' => $expiresAt]
        );


        // Отправка кода на почту
        Mail::to($user->email)->queue(new TwoFactorEnableCodeMail($confirmationCode));

        return response()->json(['message' => 'Код подтверждения отправлен на вашу почту'], 200);
    }

    public function store(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'code' => ['required', 'max:255'],
        ]);
        $user = $request->user();
        
        // Проверка, включена ли у пользователя 2FA
        if ($user->hasEnabledTwoFactorAuthentication()) {
            return response()->json(['error' => 
            'Двухфакторная аутентификация уже включена'], 422);
        }

        // Получение кода из базы данных
        $twoFactorCode = TwoFactorEnableCode::where('user_id', 
        $user->id)->first();

        if (is_null($twoFactorCode)) {
            return response()->json(['error' => 
            'Код не найден'], 422);
        }

        if ($twoFactorCode->isExpired()) {         
            return response()->json(['error' => 'Срок действия кода истек'], 422);
        }
        if (!Hash::check($request->code, $twoFactorCode->code)) {
            return response()->json(['error' => 'Недействительный код'], 422);
        }
        // Включение двухфакторной аутентификации через метод клаасса
        $enable($user, $request->boolean('force', false));
        // Удаление кода из базы данных
        $twoFactorCode->delete();
        return response()->json(['message' => 'Первый этап подключения успешно пройден.'], 200);
    }

    public function sendConfirmationCode(Request $request)
    {
        $user = $request->user();
        $confirmationCode = rand(100000, 999999); // Генерация кода
        $expiresAt = Carbon::now()->addMinutes(5); // Время действия кода

        // Сохранение кода в базу данных

        TwoFactorConfirmationDisabledCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $confirmationCode, 'expires_at' => $expiresAt]
        );


        // Отправка кода на почту
        Mail::to($user->email)->queue(new TwoFactorDisabledCode($confirmationCode));

        return response()->json(['message' => 'Код подтверждения отправлен на вашу почту'], 200);
    }
    public function destroy(Request $request)
    {
        $validationRules = [
            'password' => ['required',  'current_password',],
            'confirmation_code' => ['required', 'string', 'max:255'],
        ];

        $user = $request->user();

        // Проверка, верифицирована ли почта
        if (!$user->hasVerifiedEmail()) {
            return response()->json(['error' => 'Почта не верифицирована'], 422);
        }

        $request->validate($validationRules);

        // Проверка, включена ли у пользователя 2FA
        if (!$user->hasEnabledTwoFactorAuthentication()) {
            return response()->json(['error' => 'Двухфакторная аутентификация не включена'], 422);
        }

        if ($user->hasVerifiedEmail()) {
            // Проверка кода подтверждения
            $confirmationCodeRecord = TwoFactorConfirmationDisabledCode::where('user_id', $user->id)->first();

            if (is_null($confirmationCodeRecord)) {
                return response()->json(['error' => 'Код подтверждения не найден'], 422);
            }

            if (!Hash::check($request->confirmation_code, $confirmationCodeRecord->code)) {
                return response()->json(['error' => 'Недействительный  код подтверждения'], 422);
            }
            if ($confirmationCodeRecord->isExpired()) {
                return response()->json(['error' => 'Истёкший код подтверждения'], 422);
            }

            // Удаление кода подтверждения после успешной проверки
            $confirmationCodeRecord->delete();
        }

        // Отключение 2FA
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        TwoFactorAuthenticationDisabled::dispatch($user); // событие отключения 2FA

        return response()->json(['message' => 'Двухфакторная аутентификация успешно отключена'], 200);
    }
}
