<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class CustomLogoutResponseFortify implements LogoutResponseContract
{

      public function toResponse($request)
    {

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => 'You have been logged out.'], 200);
        }
    }
}
