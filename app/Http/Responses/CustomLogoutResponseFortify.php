<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class CustomLogoutResponseFortify implements LogoutResponseContract
{
    public function toResponse($request)
    {

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => 'Вы успешно вышли из системы!'], 200);
        }
    }
}
