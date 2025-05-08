<?php

namespace App\Services;

use App\Models\SystemError;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorHandlerService
{
    public function recordError(Throwable $exception, $request = null)
    {
        try {
            // Simplificamos el manejo del código de error
            $errorCode = 500; // Por defecto, error interno del servidor
            
            // Intentamos obtener un código si existe
            if (method_exists($exception, 'getCode')) {
                $code = $exception->getCode();
                // Solo usamos el código si es un entero válido para HTTP
                if (is_int($code) && $code >= 100 && $code < 600) {
                    $errorCode = $code;
                }
            }
            
            $data = [
                'error_code' => $errorCode,
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'user_id' => Auth::id(),
                'url' => $request ? $request->fullUrl() : null,
                'ip' => $request ? $request->ip() : null,
                'user_agent' => $request ? $request->userAgent() : null,
                'request_data' => $request ? json_encode($request->all()) : null,
            ];

            SystemError::create($data);
            
            Log::error('Error registrado en la base de datos', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
            
            return true;
        } catch (Throwable $e) {
            Log::error('Error al registrar el error en la base de datos', [
                'message' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
}