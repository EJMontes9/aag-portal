<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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

        // ── Proxies de confianza ────────────────────────────────────────────
        // Detras de Cloudflare la IP que ve Apache es la del proxy. Sin esto,
        // TODOS los visitantes comparten IP y el limite del boletin pasa a ser
        // global: cinco intentos de cualquiera y el formulario queda bloqueado
        // para todo el mundo. Ademas Laravel no detecta el HTTPS original y
        // puede generar enlaces http://.
        //
        // Por defecto no se confia en nadie (TRUSTED_PROXIES vacio), porque
        // confiar de mas permite falsear la IP con una cabecera inventada.
        // La explicacion completa esta en config/proxies.php.
        $proxies = config('proxies.trusted');

        if ($proxies === 'cloudflare') {
            $proxies = config('proxies.cloudflare');
        } elseif (is_string($proxies) && $proxies !== '' && $proxies !== '*') {
            $proxies = array_filter(array_map('trim', explode(',', $proxies)));
        }

        if (! empty($proxies)) {
            $middleware->trustProxies(
                at: $proxies,
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO,
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
