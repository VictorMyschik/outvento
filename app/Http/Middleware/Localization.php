<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class Localization
{
    public function handle($request, Closure $next)
    {
        App::setlocale($request->header('X-Locale', config('app.locale')));

        return $next($request);
    }
}
