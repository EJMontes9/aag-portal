<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Obliga HTTPS en producción: cualquier petición que llegue por HTTP se
 * redirige (301) a la misma URL en HTTPS.
 *
 * ── Por qué en código y no solo confiando en cPanel/Cloudflare ─────────────
 * El TDR exige HTTPS forzado y este proyecto ya se llevó una sorpresa con
 * depender de configuración de servidor que nadie recuerda replicar (ver
 * SecurityHeaders). Cloudflare, cuando se active, puede hacerlo también con
 * "Always Use HTTPS", pero esta capa no depende de que esa casilla siga
 * marcada.
 *
 * ── Por qué $request->secure() y no comprobar el esquema a mano ────────────
 * Detrás de un proxy (Cloudflare) la conexión real al servidor Apache/LiteSpeed
 * es HTTP aunque el visitante use HTTPS; Laravel solo sabe la verdad si confía
 * en la cabecera X-Forwarded-Proto, que es justo lo que configura
 * AppServiceProvider::configurarProxies(). Por eso este middleware no sirve de
 * nada si esa confianza en el proxy no está bien puesta.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && ! $request->secure() && ! $request->is('up')) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
