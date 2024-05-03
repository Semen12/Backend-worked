<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(Request $request): JsonResponse // изменён тип запроса, не из формы (в виде EmailRequest) а через интернет-запрос (Request)
{
    // добавлены ответы в виде кодов и собщениями
    if ($request->user()->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified'], 200);
    }

    if ($request->user()->markEmailAsVerified()) {
        event(new Verified($request->user())); // необязательное событие
        return response()->json(['message' => 'Email verified successfully'], 200);
    }

    return response()->json(['message' => 'Email verification failed'], 500);
}
}
