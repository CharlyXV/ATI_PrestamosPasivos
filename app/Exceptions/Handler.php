<?php

namespace App\Exceptions;

use App\Services\ErrorHandlerService;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $exception, Request $request) {
            // Si es una solicitud AJAX o API
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($exception, $request);
            }

            // Para los errores HTTP 500 (Internal Server Error)
            if ($this->isHttpException($exception) && $this->getExceptionStatusCode($exception) == 500) {
                return $this->handleWebException($exception, $request);
            }

            // Para errores generales no HTTP
            if (!$this->isHttpException($exception)) {
                // Registramos el error
                try {
                    $errorHandler = new ErrorHandlerService();
                    $errorHandler->recordError($exception, $request);
                } catch (Throwable $e) {
                    // Si falla el registro, al menos lo registramos en los logs
                    Log::error('Error al registrar excepción', [
                        'exception' => $e->getMessage(),
                    ]);
                }

                // En producción mostramos la vista de error personalizada
                if (App::environment('production')) {
                    return response()->view('errors.500', [
                        'error_id' => time(), // Usar timestamp como ID de referencia temporal
                    ], 500);
                }
            }
            
            return null; // Continuar con el manejo normal de excepciones de Laravel
        });
    }

    private function handleApiException(Throwable $exception, Request $request)
    {
        // Registramos el error
        try {
            $errorHandler = new ErrorHandlerService();
            $errorHandler->recordError($exception, $request);
        } catch (Throwable $e) {
            // Si falla el registro, al menos lo registramos en los logs
            Log::error('Error al registrar excepción API', [
                'exception' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => App::environment('production') 
                ? 'Ha ocurrido un error interno en el servidor. Por favor, contacte a soporte técnico.'
                : $exception->getMessage(),
            'error_id' => time(), // Usar timestamp como ID de referencia
        ], 500);
    }

    private function handleWebException(Throwable $exception, Request $request)
    {
        // Registramos el error
        try {
            $errorHandler = new ErrorHandlerService();
            $errorHandler->recordError($exception, $request);
        } catch (Throwable $e) {
            // Si falla el registro, al menos lo registramos en los logs
            Log::error('Error al registrar excepción web', [
                'exception' => $e->getMessage(),
            ]);
        }

        // Si la solicitud es de Filament
        if ($request->is('admin/*')) {
            // Para Filament usamos sus notificaciones nativas
            Notification::make()
                ->title('Error del sistema')
                ->body('Ha ocurrido un error interno en el servidor. Por favor, contacte a soporte técnico para la resolución del problema.')
                ->danger()
                ->persistent()
                ->send();
                
            return back();
        }

        // Para las demás solicitudes web
        return response()->view('errors.500', [
            'error_id' => time(),
        ], 500);
    }

    private function getExceptionStatusCode(Throwable $exception): int
    {
        if ($this->isHttpException($exception)) {
            return $exception->getStatusCode();
        }
    
        // Personaliza códigos para excepciones conocidas
        if ($exception instanceof \PDOException) {
            return 503; // Service Unavailable (para errores de BD)
        }
    
        return 500; // Por defecto
    }
}

