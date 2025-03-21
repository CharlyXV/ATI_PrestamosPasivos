<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomThrottle
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, $maxAttempts = 1000, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'error' => true,
                'success' => false,
                'errorMessage' => 'Ha realizado muchas solicitudes, intente de nuevo más tarde.'
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        return $next($request);
    }

    protected function resolveRequestSignature(Request $request)
    {
        // Personaliza la clave basada en el IP y el usuario autenticado (si existe)
        $userId = $request->user() ? $request->user()->id : 'guest';
        return sha1($request->ip() . '|' . $userId);
    }
}