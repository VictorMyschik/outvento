<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class HandleVerified extends RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        /** @var User $user */
        $user = auth()->user();

        if (is_null($user->email_verified_at)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Данный ресурс недоступен для пользователей с неподтверждённым адресом электронной почты.'
                ], 403);
            }

            throw new UnauthorizedHttpException('Данный ресурс недоступен для пользователей с неподтверждённым адресом электронной почты.');
        }

        return $next($request);
    }
}
