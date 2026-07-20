<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Convierte los 404 en redirecciones cuando la direccion pedida es una de las
 * antiguas del WordPress.
 *
 * ── Por que se comprueba DESPUES y no antes ─────────────────────────────────
 * Lo intuitivo seria mirar la tabla al principio de cada peticion, pero eso
 * haria trabajo de mas en el 99,9% de las visitas, que van a paginas que si
 * existen. Aqui se deja pasar la peticion y solo se mira la tabla si el portal
 * ha respondido 404: el coste recae unicamente sobre las direcciones que ya
 * han fallado.
 *
 * ── Por que no se confia a ciegas en el destino ─────────────────────────────
 * El destino lo escribe una persona desde el panel. Si una cuenta del panel se
 * viera comprometida, poder redirigir a cualquier sitio convertiria el dominio
 * institucional en un trampolin de phishing: un enlace que empieza por
 * aag.org.ec y termina en una copia falsa de un banco. Por eso solo se admiten
 * rutas internas y direcciones https, y se rechaza cualquier otro esquema
 * (javascript:, data:, //otro-dominio...).
 */
class RedirigirRutasAntiguas
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 404) {
            return $response;
        }

        // Solo GET y HEAD: redirigir un POST perderia el cuerpo de la peticion,
        // y quien envia un formulario a una direccion que ya no existe merece el
        // 404 y no un envio silencioso a otra parte.
        if (! $request->isMethodSafe()) {
            return $response;
        }

        $origen  = Redirect::normalizar($request->getPathInfo());
        $destinos = Redirect::activas();

        if (! isset($destinos[$origen])) {
            return $response;
        }

        $destino = $destinos[$origen];

        if (! $this->destinoSeguro($destino)) {
            return $response;
        }

        // El contador se actualiza sin disparar eventos del modelo (ver
        // Redirect::registrarUso), pero hace falta el registro para conocer su
        // id y su codigo. Se busca aqui, no en activas(), porque esto solo
        // ocurre en las visitas que de verdad se redirigen.
        $registro = Redirect::query()->where('from_path', $origen)->first();

        if (! $registro) {
            return $response;
        }

        $registro->registrarUso();

        return redirect()->to($destino, $registro->status_code);
    }

    /**
     * Admite rutas internas ("/nosotros") y direcciones https completas.
     * Rechaza todo lo demas, incluidas las que empiezan por "//", que el
     * navegador interpreta como otro dominio heredando el esquema actual.
     */
    protected function destinoSeguro(string $destino): bool
    {
        if (str_starts_with($destino, '//')) {
            return false;
        }

        if (str_starts_with($destino, '/')) {
            return true;
        }

        return str_starts_with(mb_strtolower($destino), 'https://');
    }
}
