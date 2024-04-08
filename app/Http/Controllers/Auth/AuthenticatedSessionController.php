<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
//use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
//use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use \Symfony\Component\HttpFoundation\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();  /* проверяет учетные данные пользователя из запроса. Если учетные данные верны, пользователь успешно аутентифицируется в системе. Если учетные данные неверны, 
        будет сгенерировано исключение и сгенерируется сообщение об ошибке аутентификации. */

        $request->session()->regenerate();
        /* После успешной аутентификации происходит обновление идентификатора сеанса. Это делается для обеспечения безопасности приложения,
         чтобы предотвратить атаки с подделкой запросов. */

        return response()->json(
            [
                'message' => 'Successfully logged in',
                'user' => $request->user()
            ],
            Response::HTTP_OK,
            $request->filled('errors') ? $request->get('errors') : [],
            JSON_UNESCAPED_UNICODE
        );
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        Auth::guard('web')->logout(); /* Эта строка отвечает за выход пользователя из системы. Она использует метод logout() для аутентификационного экземпляра по умолчанию web. 
        После выполнения этой строки пользователь больше не будет аутентифицирован в системе. */

        $request->session()->invalidate(); /*  Этот вызов инвалидирует текущую сессию пользователя. Это означает, что сессия пользователя будет помечена как недействительная, 
        и после выхода из системы текущая сессия не будет больше действительной. */

        $request->session()->regenerateToken(); //  генерирует новый CSRF-токен (Cross-Site Request Forgery) 

        return response()->json(
            [
                'message' => 'Successfully logged out',
            ],
            Response::HTTP_OK,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

  

}
