<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NoticiaResource;
use App\Http\Resources\Api\V1\NoticiaResumenResource;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Noticias publicadas, en solo lectura.
 *
 * El criterio de publicacion es el scope published() del modelo — el mismo que
 * usan NewsController y SearchController. Es importante que sea LITERALMENTE
 * el mismo y no una condicion reescrita aqui: si el dia de manana cambia la
 * definicion de "publicada" (por ejemplo, anadiendo una fecha de despublicacion)
 * y este controlador llevara su propia copia, la API seguiria sirviendo lo que
 * la web ya no muestra.
 */
class NewsApiController extends Controller
{
    /** Listado paginado, de la mas reciente a la mas antigua. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $noticias = News::query()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->paginate(config('api.per_page'));

        return NoticiaResumenResource::collection($noticias);
    }

    /**
     * Detalle por slug.
     *
     * No se llama a incrementViews(): las visitas cuentan lo que la gente lee
     * en la web, y mezclar ahi las lecturas de un robot de sincronizacion
     * falsearia la unica metrica de audiencia que tiene el portal.
     */
    public function show(string $slug): NoticiaResource
    {
        $noticia = News::query()
            ->published()
            ->with('category')
            ->where('slug', $slug)
            ->first();

        // abort() en vez de firstOrFail(): el 404 automatico de Eloquent
        // responde "No query results for model [App\Models\News]", que le
        // cuenta a un extrano como se llaman por dentro nuestras clases.
        abort_unless($noticia, 404, 'Noticia no encontrada.');

        return new NoticiaResource($noticia);
    }
}
