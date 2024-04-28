<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
//use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use \Symfony\Component\HttpFoundation\Response;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'], // обязательно для заполнения
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            // почта обязательна для заполнения, маленькие буквы строка, максимальная длина 255, уникальная
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            //пароль обязателен для заполнения, повторить пароль обязателен для заполнения
            // валидация мастер пароля
            'master_password'=>['required','confirmed','min:6','different:password'],
        ] ,[
            'master_password.different' => 'Мастер пароль должен отличаться от пароля для входа в аккаунт',
        ] );

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'master_password' => Hash::make($request->master_password),
        ]);

        event(new Registered($user)); // событие регистрации пользователя для использования в системе

        Auth::login($user); // автоматическая утентификация пользователя при регистрации
       // $token = $user->createToken('api-front-token')->has;
        return response()->json(
            [
                'message' => 'User registered successfully',
                'user' => $user,
            ],
            Response::HTTP_CREATED,
            $request->filled('errors') ? $request->get('errors') : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }
}
