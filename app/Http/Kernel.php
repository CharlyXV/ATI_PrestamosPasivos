<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // Otros middlewares globales
        \App\Http\Middleware\LanguageMiddleware::class,
        \App\Http\Middleware\SetLanguage::class,
    ];
    

   
    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // Middlewares del grupo web
            \App\Http\Middleware\LanguageMiddleware::class,
        ],

        'api' => [
            // Middlewares del grupo API
        ],
    ];

    // Otros métodos y propiedades según sea necesario
}
