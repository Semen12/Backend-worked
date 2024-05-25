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
use Symfony\Component\HttpFoundation\Response;

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
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        Auth::login($user); // автоматическая аутентификация пользователя при регистрации
        event(new Registered($user)); // событие регистрации пользователя для использования в системе

        return response()->json(
            [
                'message' => 'Вы успешно зарегистрировались!',
                'user' => $user,
            ],
            Response::HTTP_CREATED,
            $request->filled('errors') ? $request->get('errors') : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }
}
