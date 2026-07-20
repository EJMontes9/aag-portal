<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Redirige usuarios invitados al login de Filament
        $middleware->redirectGuestsTo(fn () => route('filament.admin.auth.login'));

        // Cabeceras de seguridad en todas las respuestas web.
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Los proxies de confianza NO se configuran aqui: este closure corre
        // mientras se construye la aplicacion, cuando el contenedor todavia no
        // tiene registrado el servicio 'config', y cualquier llamada a config()
        // revienta con "Target class [config] does not exist" tumbando el sitio
        // entero. Se hace en AppServiceProvider::boot(), que si tiene config.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
