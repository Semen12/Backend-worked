<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class CheckMasterPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $masterPasswordVerifiedAt = $request->session()->get('master_password_verified_at');

        if (! $request->session()->has('master_password_verified') ||
            ! $request->session()->get('master_password_verified') ||
            ! $masterPasswordVerifiedAt ||
            Carbon::parse($masterPasswordVerifiedAt)->addMinutes(30)->isPast()) {

            $request->session()->forget(['master_password_verified', 'master_password_verified_at']);

            return response()->json(['error' => 'Мастер-пароль не подтверждён'], 403);
        }

        return $next($request);
    }
}
