<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ConvocatoriaResource;
use App\Http\Resources\Api\V1\ConvocatoriaResumenResource;
use App\Models\Convocatoria;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Convocatorias publicas, en solo lectura.
 *
 * Se sirven los mismos estados que la web: 'vigente' y 'cerrada'. Los
 * borradores quedan fuera — no tienen ficha publica, y su titulo o su resumen
 * pueden anticipar un proceso que aun no se ha anunciado. Es el mismo criterio
 * que ConvocatoriaController::show() y SearchController.
 */
class ConvocatoriaApiController extends Controller
{
    /**
     * Estados con ficha publica. Se declara una sola vez para que el listado y
     * el detalle no puedan divergir.
     */
    private const ESTADOS_PUBLICOS = ['vigente', 'cerrada'];

    /** Listado paginado, de cierre mas proximo a mas lejano en el pasado. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $convocatorias = Convocatoria::query()
            ->whereIn('status', self::ESTADOS_PUBLICOS)
            ->orderByDesc('closes_at')
            ->paginate(config('api.per_page'));

        return ConvocatoriaResumenResource::collection($convocatorias);
    }

    /** Detalle por slug. */
    public function show(string $slug): ConvocatoriaResource
    {
        $convocatoria = Convocatoria::query()
            ->whereIn('status', self::ESTADOS_PUBLICOS)
            ->where('slug', $slug)
            ->first();

        // Mismo criterio que en las noticias: el 404 de Eloquent revelaria el
        // nombre interno del modelo.
        abort_unless($convocatoria, 404, 'Convocatoria no encontrada.');

        return new ConvocatoriaResource($convocatoria);
    }
}
