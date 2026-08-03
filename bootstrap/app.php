<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        // ──────────────────────────────────────────────────────────────────────
        // API publica: se registra SOLO si esta activada.
        //
        // ── Por que en `then:` y no en el parametro `api:` ──────────────────
        // El parametro `api:` se decide AQUI, mientras se construye la
        // aplicacion, y en ese momento no hay forma fiable de saber si la API
        // esta encendida:
        //
        //   - config() todavia no existe. El contenedor aun no tiene registrado
        //     el servicio 'config' y la llamada lanza "Target class [config]
        //     does not exist", que no deja arrancar NADA: el sitio entero
        //     responde 500. Ya paso una vez en este proyecto con los proxies
        //     (ver AppServiceProvider::configurarProxies).
        //
        //   - Env::get() tampoco vale. Con config:cache aplicado Laravel se
        //     salta la lectura del .env, asi que devolveria null SIEMPRE y la
        //     API no se encenderia nunca en produccion, que es justo donde la
        //     cache se usa.
        //
        // `then:` en cambio es un callback que Laravel guarda ahora y ejecuta
        // despues, al registrar las rutas, con la aplicacion ya arrancada y la
        // configuracion cargada (venga del .env o de la cache). Ahi config() es
        // seguro, y es tambien lo que permite que `php artisan route:cache`
        // congele el estado correcto.
        //
        // El resultado es el buscado: con la API apagada las rutas no llegan a
        // existir en la tabla de rutas y /api/* devuelve 404 — no 401, que le
        // confirmaria a quien sondea que el endpoint esta ahi.
        // ──────────────────────────────────────────────────────────────────────
        then: function () {
            if (config('api.enabled')) {
                Route::middleware('api')
                    ->prefix('api')
                    ->group(base_path('routes/api.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Redirige usuarios invitados al login de Filament
        $middleware->redirectGuestsTo(fn () => route('filament.admin.auth.login'));

        // Cabeceras de seguridad en todas las respuestas web.
        //
        // RedirigirRutasAntiguas va DESPUES de SecurityHeaders a proposito: solo
        // actua cuando la respuesta ya es un 404, asi que cuanto mas tarde se
        // ejecute, menos trabajo hace en las visitas normales.
        $middleware->web(append: [
            \App\Http\Middleware\ForceHttps::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\RedirigirRutasAntiguas::class,
        ]);

        // Los proxies de confianza NO se configuran aqui: este closure corre
        // mientras se construye la aplicacion, cuando el contenedor todavia no
        // tiene registrado el servicio 'config', y cualquier llamada a config()
        // revienta con "Target class [config] does not exist" tumbando el sitio
        // entero. Se hace en AppServiceProvider::boot(), que si tiene config.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Los errores bajo /api/* se responden SIEMPRE en JSON, aunque el
        // cliente no mande cabecera Accept.
        //
        // Sin esto pasan dos cosas indeseables. La primera es que un fallo de
        // autenticacion no devuelve 401: como arriba hay un redirectGuestsTo
        // al login de Filament, una peticion sin token acaba en un 302 hacia
        // una pantalla HTML de inicio de sesion, que no es una respuesta que
        // un cliente de API pueda interpretar. La segunda es que un 404 o un
        // 429 devolverian la pagina de error del portal, con su HTML y sus
        // hojas de estilo, en lugar de un cuerpo que se pueda leer.
        $exceptions->shouldRenderJsonWhen(
            fn ($request, $e) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
