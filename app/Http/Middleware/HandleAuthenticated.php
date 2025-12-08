<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class HandleAuthenticated extends RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Данный ресурс недоступен для авторизованных пользователей.'
                    ], 403);
                }

                throw new UnauthorizedHttpException('Данный ресурс недоступен для авторизованных пользователей.');
            }
        }

        return $next($request);
    }
}
