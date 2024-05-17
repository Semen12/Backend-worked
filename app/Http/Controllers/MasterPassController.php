<?php

namespace App\Http\Controllers;

use App\Models\MasterPasswordToken;
use App\Notifications\ResetMasterPasswordNotification;
use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MasterPassController extends Controller
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
        $user=$request->user();
        if ($user->master_password !== null) {
            return response()->json(['error' => 'Мастер-пароль уже установлен'], 422);
        }


        $validData= $request->validate([
            'master_password'=>['required','confirmed','min:6',
                function ($attribute, $value, $fail) use ($request) {
                // Пользовательское  правило для проверки отличия от текущего пароля и мастер-пароля
                      if (Hash::check($value,$request->user()->password) ) {
                        $fail('Мастер-пароль должен отличаться от пароля учетной записи');
                    }
                }
                ],

        ]);
        $user->master_password=Hash::make($validData['master_password']);
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
            'master_password' => ['required','min:6', function ($attribute, $value, $fail) use ($request) {
                if (!Hash::check($value, $request->user()->master_password)) {
                    $fail('Текущий мастер-пароль не верен');
                }
            }],
            'new_master_password' => ['required', 'confirmed', 'min:6', 'different:master_password',
                function ($attribute, $value, $fail) use ($request) {
                    if(Hash::check($value, $request->user()->password)) {
                        $fail('Новый мастер-пароль должен отличаться от пароля учетной записи');
                    }
                }],
        ], [
            'new_master_password.different' => 'Новый мастер-пароль должен отличаться от текущего мастер-пароля',
        ]);

        $user->update(['master_password'=>Hash::make($validData['new_master_password'])]);

        return response()->json(['message' => 'Мастер-пароль обновлен'], 200);


    }


    //отправляем ссылку для восстановления пароля на почту
    public function resetLink(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password']
        ]);
        $user = $request->user();
        $token =  Str::lower(Str::random(64));
        $expiredAt = Carbon::now()->addMinutes(60);
        $resetToken = MasterPasswordToken::updateOrCreate(
            ['user_id' => $user->id],
            ['token' => Hash::make($token), 'expired_at' => $expiredAt]);

        if($resetToken){
            $user->notify(new ResetMasterPasswordNotification($token));
            return response()->json(['message' => 'Ссылка для восстановления мастер-парпароля отправлена на почту'], 200);
        }

       return response()->json(['message' => 'Ссылка для восстановления мастер-парпароля не отправлена. Что-то пошло не так'], 500);


    }

    public function reset(Request $request){
        if($request->user()->id != $request->id) {
            return response()->json(['message' => 'Ссылка для данного пользователя недействительна. Отправьте письмо повторно.'], 403);
        }
        $user = $request->user();
        $request->validate([
            'master_password' => ['required','min:6','confirmed', function ($attribute, $value, $fail) use ($request) {
                if (Hash::check($value, $request->user()->password)) {
                    $fail('Мастер-пароль не должен совпадать с паролем учетной записи');
                }
            }],
        ]);


        $resetToken = MasterPasswordToken::where('user_id', $user->id)->first();
        if (!$resetToken || !Hash::check($request->token, $resetToken->token)) {
            return response()->json(['message' => 'Недействительный токен для сброса мастер-пароля'], 422);
        }
        if (Carbon::now()->gt($resetToken->expired_at)) {
            $resetToken->delete();
            // Срок действия токена истек
            return response()->json(['message' => 'Срок действия токена сброса мастер-пароля истек, отправьте письмо для сброса повторно'], 422);
        }

        $user->update(['master_password'=>Hash::make($request->master_password)]);
        $resetToken->delete();

        return response()->json(['message' => 'Мастер-пароль успешно изменен'], 200);


    }

        /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
