<?php
 
use Illuminate\Foundation\Application;
// Importa la clase `Application` de Laravel, que representa la aplicación principal.

use Illuminate\Foundation\Configuration\Exceptions;
// Importa la clase `Exceptions` para manejar excepciones de configuración.

use Illuminate\Foundation\Configuration\Middleware;
// Importa la clase `Middleware` para registrar middlewares en la aplicación.

use Illuminate\Auth\AuthenticationException;
// Importa la clase `AuthenticationException` para manejar excepciones de autenticación.

use Illuminate\Http\Request;
// Importa la clase `Request` para manejar solicitudes HTTP.
 
return Application::configure(basePath: dirname(__DIR__))
    // Configura la aplicación Laravel, estableciendo la ruta base del proyecto.

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // Define el archivo de rutas para las rutas web.
        api: __DIR__.'/../routes/api.php',
        // Define el archivo de rutas para las rutas de la API.
        commands: __DIR__.'/../routes/console.php',
        // Define el archivo de rutas para los comandos de consola.
        health: '/up',
        // Define una ruta de verificación de estado (health check) en `/up`.
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registra middlewares personalizados en la aplicación.
        $middleware->alias([

            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            // Registra el middleware de autenticación con el alias `auth`.
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            // Registra el middleware de roles de Spatie con el alias `role`.
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Configura el manejo de excepciones en la aplicación.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
        // Define cómo se renderizan las excepciones de autenticación.
            if ($request->is('api/*')) {
        // Verifica si la solicitud pertenece a la API.
                return response()->json([
                    'message' => $e->getMessage(),
                ], 401);
                // Retorna una respuesta JSON con un mensaje de error y un código de estado 401.
            }
        });
    })->create();
// Crea y retorna la instancia de la aplicación configurada.