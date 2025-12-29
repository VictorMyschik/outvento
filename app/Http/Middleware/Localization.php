<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class Localization
{
    public function handle($request, Closure $next)
    {
        $locale = Session::get('locale') ?: Cookie::get('locale');

        if ($locale) {
            App::setlocale($locale);
        }

        return $next($request);
    }
}
