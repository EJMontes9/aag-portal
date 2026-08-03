<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad de la respuesta.
 *
 * Va en middleware (código) y no en el .htaccess a propósito: así viaja con el
 * repositorio y se aplica igual en local, en cPanel o donde se despliegue, sin
 * depender de que alguien recuerde configurar el servidor.
 *
 * El portal no tenía ninguna de estas cabeceras.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Evita que el navegador "adivine" el tipo de un archivo por su
        // contenido. Es lo que impide que algo subido como .txt acabe
        // interpretándose como HTML o JavaScript.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Sin esto, el panel /admin puede empotrarse en un iframe de otro
        // sitio y superponerle controles invisibles (clickjacking).
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // No filtrar la URL completa (que puede llevar identificadores) al
        // navegar a otro dominio.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // El portal no usa cámara, micrófono ni geolocalización.
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), interest-cohort=()'
        );

        // Oculta la versión de PHP, que facilita buscar exploits conocidos.
        $response->headers->remove('X-Powered-By');

        // HSTS: solo bajo HTTPS. Enviarlo por HTTP no tiene efecto, y en local
        // (donde no hay certificado) dejaría el dominio inaccesible en el
        // navegador durante el max-age. Por eso se condiciona.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // ── Content-Security-Policy ─────────────────────────────────────────
        // Es la defensa de fondo contra el XSS: aunque se cuele HTML en un
        // contenido, el navegador no ejecuta scripts de otros orígenes.
        //
        // 'unsafe-inline' y 'unsafe-eval' son ambos necesarios. Conviene leer
        // esto antes de intentar quitarlos "por endurecer":
        //
        //   'unsafe-inline' -> Alpine y Livewire usan atributos en línea, y el
        //                      portal inyecta las variables de color en un
        //                      <style> generado en cada petición.
        //
        //   'unsafe-eval'   -> Alpine 3 compila CADA expresión (x-show, x-data,
        //                      :class...) con new Function(), también cuando se
        //                      sirve compilado por Vite. Sin esta directiva el
        //                      navegador lo bloquea y Alpine falla a medias:
        //                      retira los x-cloak pero no evalúa los x-show, de
        //                      modo que todo queda visible a la vez.
        //
        //                      Ocurrió de verdad: el navegador de transparencia
        //                      mostraba los cuatro años apilados (enero a
        //                      diciembre repetido cuatro veces) en vez de uno.
        //                      Afectaba igual al menú móvil, al acordeón de FAQ,
        //                      al slider y al contador de convocatorias.
        //
        //                      El síntoma es silencioso: la página carga sin
        //                      errores visibles y solo se ve en la consola del
        //                      navegador ("Alpine Expression Error: ...
        //                      'unsafe-eval' is not an allowed source"). Si
        //                      alguna vez algo interactivo deja de responder,
        //                      mirar ahí primero.
        //
        // Con ambas directivas la CSP no detiene un XSS en línea, pero sigue
        // aportando: limita DE DONDE se puede cargar código y, sobre todo,
        // A DONDE se pueden enviar los datos robados (connect-src). Quitarlas
        // exigiría migrar a la build CSP de Alpine, que obliga a reescribir
        // todas las expresiones como métodos de un componente.
        //
        // En el panel /admin no se aplica: Filament genera scripts propios y
        // una CSP restrictiva lo rompe.
        if (! $request->is('admin', 'admin/*', 'livewire/*')) {
            // El subdominio de documentos (LOTAIP) es un origen distinto. Los
            // enlaces de descarga son navegaciones, que la CSP no bloquea, pero
            // se declara igualmente para que las miniaturas o vistas previas
            // que se añadan más adelante no queden cortadas sin explicación.
            $origenDocumentos = '';
            try {
                $base = trim((string) settings('documents_base_url', ''));
                if ($base !== '' && ($host = parse_url($base, PHP_URL_HOST))) {
                    $esquema = parse_url($base, PHP_URL_SCHEME) ?: 'https';
                    $origenDocumentos = ' ' . $esquema . '://' . $host;
                }
            } catch (\Throwable) {
                // Si la tabla de ajustes aún no existe (instalación nueva) se
                // sigue sin el origen extra en vez de romper la respuesta.
            }

            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://www.google-analytics.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' data: https://fonts.gstatic.com",
                "img-src 'self' data: https:",
                // A donde puede hablar la pagina. Es lo que impide exfiltrar
                // datos a un servidor del atacante.
                "connect-src 'self' https://www.google-analytics.com" . $origenDocumentos,
                // Mapas y videos embebidos.
                "frame-src 'self' https://www.google.com https://maps.google.com https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com",
                // Nadie puede empotrarnos a nosotros (equivale a X-Frame-Options).
                "frame-ancestors 'self'",
                // No hay formularios que envíen a terceros.
                "form-action 'self'",
                "base-uri 'self'",
                "object-src 'none'",
            ]));
        }

        return $response;
    }
}
