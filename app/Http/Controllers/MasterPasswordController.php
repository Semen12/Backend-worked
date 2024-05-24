<?php

namespace App\Http\Controllers;

use App\Models\MasterPasswordToken;
use App\Notifications\ResetMasterPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class MasterPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->master_password !== null) {
            return response()->json(['error' => 'Мастер-пароль уже установлен'], 422);
        }

        $validData = $request->validate([
            'master_password' => ['required', 'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(), 'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    // Пользовательское  правило для проверки отличия от текущего пароля и мастер-пароля
                    if (Hash::check($value, $request->user()->password)) {
                        $fail('Мастер-пароль должен отличаться от пароля учетной записи');
                    }
                },
            ],

        ]);
        $user->master_password = $validData['master_password'];
        $user->save();

        return response()->json(['message' => 'Мастер-пароль успешно установлен'], 201);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
        $user = $request->user();
        $validData = $request->validate([
            'master_password' => ['required', 'min:6', 'max:255', function ($attribute, $value, $fail) use ($request) {
                if (! Hash::check($value, $request->user()->master_password)) {
                    $fail('Текущий мастер-пароль не верен');
                }
            }],
            'new_master_password' => ['required', 'confirmed', Password::min(6)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(),
                'max:255', 'different:master_password',
                function ($attribute, $value, $fail) use ($request) {
                    if (Hash::check($value, $request->user()->password)) {
                        $fail('Новый мастер-пароль должен отличаться от пароля учетной записи');
                    }
                }],
        ], [
            'new_master_password.different' => 'Новый мастер-пароль должен отличаться от текущего мастер-пароля',
        ]);

        $user->update(['master_password' => $validData['new_master_password']]);

        return response()->json(['message' => 'Мастер-пароль обновлен'], 200);

    }

    //отправляем ссылку для восстановления пароля на почту
    public function resetLink(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);
        $user = $request->user();
        $token = Str::lower(Str::random(64));
        $expiredAt = Carbon::now()->addMinutes(60);
        $resetToken = MasterPasswordToken::updateOrCreate(
            ['user_id' => $user->id],
            ['token' => $token, 'expired_at' => $expiredAt]);

        if ($resetToken) {
            $user->notify(new ResetMasterPasswordNotification($token));

            return response()->json(['message' => 'Ссылка для восстановления мастер-пароля отправлена на почту'], 200);
        }

        return response()->json(['error' => 'Ссылка для восстановления мастер-пароля не отправлена. Что-то пошло не так'], 500);

    }

    public function reset(Request $request)
    {
        if ($request->user()->id != $request->id) {
            return response()->json(['error' => 'Ссылка для данного пользователя недействительна. Отправьте письмо повторно.'], 403);
        }
        $user = $request->user();
        $request->validate([
            'master_password' => ['required', Password::min(6)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(), 'confirmed', function ($attribute, $value, $fail) use ($request) {
                    if (Hash::check($value, $request->user()->password)) {
                        $fail('Мастер-пароль не должен совпадать с паролем учетной записи');
                    }
                }],
        ]);

        $resetToken = MasterPasswordToken::where('user_id', $user->id)->first();
        if (! $resetToken || ! Hash::check($request->token, $resetToken->token)) {
            return response()->json(['error' => 'Недействительный токен для сброса мастер-пароля'], 422);
        }
        if (Carbon::now()->gt($resetToken->expired_at)) {
            $resetToken->delete();

            // Срок действия токена истек
            return response()->json(['error' => 'Срок действия токена сброса мастер-пароля истек, отправьте письмо для сброса повторно'], 422);
        }

        $user->update(['master_password' => $request->master_password]);
        $resetToken->delete();

        return response()->json(['message' => 'Мастер-пароль успешно изменен'], 200);

    }

    public function setMasterPassword(Request $request) // данная функция
    // позволяет использовать механизмы сессии для установки статуса мастер пароля
    {
        $request->validate([
            'master_password' => 'required|string|min:6|max:255',
        ]);

        $user = $request->user();

        if (Hash::check($request->input('master_password'), $user->master_password)) {
            $request->session()->put('master_password_verified', true);
            $request->session()->put('master_password_verified_at', now());

            return response()->json(['message' => 'Мастер-пароль подтверждён'], 200);
        }

        return response()->json(['error' => 'Недействительный мастер-пароль'], 403);
    }

    public function checkMasterPassword(Request $request)// Проверка, установлен ли мастер-пароль в сессии и его срок действия
    {
        $isVerified = $request->session()->get('master_password_verified', false);
        $masterPasswordVerifiedAt = $request->session()->get('master_password_verified_at');

        if ($isVerified && $masterPasswordVerifiedAt && Carbon::parse($masterPasswordVerifiedAt)->addMinutes(2)->isFuture()) {
            return response()->json(['master_password' => true], 200);
        } else {
            $request->session()->forget(['master_password_verified', 'master_password_verified_at']);

            return response()->json(['master_password' => false], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
