<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class LanguageMiddleware
{
    public function handle($request, Closure $next)
    {
        $locale = $request->getPreferredLanguage(['en', 'es']);
        App::setLocale($locale);
        return $next($request);
    }
}

