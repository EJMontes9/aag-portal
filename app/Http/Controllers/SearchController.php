<?php

namespace App\Http\Controllers;

use App\Models\Convocatoria;
use App\Models\Faq;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Buscador global del portal.
 *
 * Consulta los cinco tipos de contenido público y los funde en un único
 * listado. No hay motor de indexación (Scout/Meilisearch) detrás: con el
 * volumen de este portal —unos pocos cientos de filas por tabla— un LIKE
 * '%texto%' resuelve en milisegundos y ahorra montar y mantener un servicio
 * aparte. Si el contenido crece un orden de magnitud, este es el punto por el
 * que habría que sustituirlo.
 */
class SearchController extends Controller
{
    /**
     * Tope de filas que se traen POR MODELO antes de fundir los resultados.
     *
     * El LIKE con comodín por delante no puede usar índice, así que recorre la
     * tabla entera; el límite acota lo que viaja a PHP y lo que se ordena en
     * memoria. Quien necesite más de 50 resultados de un mismo tipo no está
     * buscando, está navegando: para eso están los listados de cada sección.
     */
    private const LIMITE_POR_MODELO = 50;

    /** Resultados por página del listado fundido. */
    private const POR_PAGINA = 10;

    /**
     * Orden en que se presentan los grupos. Los resultados se ordenan por este
     * criterio ANTES de paginar, de modo que cada grupo queda contiguo y no se
     * parte en trozos sueltos repartidos por varias páginas.
     */
    private const ORDEN_TIPOS = ['noticia', 'convocatoria', 'proyecto', 'pagina', 'faq'];

    public function index(Request $request)
    {
        // 'q' tiene que ser una cadena: si llega como array (?q[]=x) el LIKE
        // reventaría con un TypeError, así que eso se rechaza de plano.
        $request->validate([
            'q' => ['nullable', 'string'],
        ]);

        // El largo, en cambio, se RECORTA en vez de rechazarse. Un validate()
        // con max:100 devuelve un 302 a la portada, y quien pega un párrafo de
        // más de 100 caracteres acaba en una página que no pidió, sin ver el
        // error. Recortar da un resultado útil y cumple igual el objetivo del
        // límite, que es no mandar cadenas enormes a un LIKE sobre columnas de
        // texto largo. Mismo criterio que NewsController.
        $q = trim((string) $request->query('q', ''));
        $q = mb_substr($q, 0, 100);

        // Sin término no se toca la base de datos: se pinta la página con el
        // campo vacío y las sugerencias.
        if ($q === '') {
            return view('pages.search.index', [
                'q' => '',
                'resultados' => $this->paginadorVacio($request),
                'grupos' => collect(),
                'total' => 0,
            ]);
        }

        $todos = collect()
            ->concat($this->buscarNoticias($q))
            ->concat($this->buscarConvocatorias($q))
            ->concat($this->buscarProyectos($q))
            ->concat($this->buscarPaginas($q))
            ->concat($this->buscarFaqs($q))
            ->sortBy(fn (array $r) => array_search($r['tipo'], self::ORDEN_TIPOS, true))
            ->values();

        $resultados = $this->paginar($todos, $request);

        return view('pages.search.index', [
            'q' => $q,
            'resultados' => $resultados,
            // Se agrupa SOLO la página actual: el listado completo ya viene
            // ordenado por tipo, así que los grupos salen intactos.
            'grupos' => collect($resultados->items())->groupBy('tipo'),
            'total' => $todos->count(),
        ]);
    }

    // ── Consultas por modelo ────────────────────────────────────────────────
    // Todas seleccionan solo las columnas que la vista usa y respetan el
    // criterio de publicación de su modelo. Un buscador que devolviera
    // borradores sería una fuga de información: el contenido no publicado no
    // tiene URL pública, pero su título y su entradilla sí se verían aquí.

    private function buscarNoticias(string $q): Collection
    {
        return News::query()
            ->published()
            ->select('id', 'title', 'slug', 'excerpt', 'published_at')
            ->where(fn ($x) => $x->where('title', 'like', "%{$q}%")
                                 ->orWhere('excerpt', 'like', "%{$q}%"))
            ->latest('published_at')
            ->limit(self::LIMITE_POR_MODELO)
            ->get()
            ->map(fn (News $n) => [
                'tipo' => 'noticia',
                'titulo' => (string) $n->title,
                'texto' => (string) $n->excerpt,
                'url' => route('news.show', $n->slug),
                'fecha' => $n->published_at?->format('d.m.Y'),
            ]);
    }

    private function buscarConvocatorias(string $q): Collection
    {
        return Convocatoria::query()
            // Mismo criterio que ConvocatoriaController::show(): 'borrador'
            // queda fuera, pero una convocatoria cerrada sigue teniendo ficha
            // pública y es información que la gente busca.
            ->whereIn('status', ['vigente', 'cerrada'])
            ->select('id', 'title', 'slug', 'short_description', 'closes_at')
            ->where(fn ($x) => $x->where('title', 'like', "%{$q}%")
                                 ->orWhere('short_description', 'like', "%{$q}%"))
            ->orderByDesc('closes_at')
            ->limit(self::LIMITE_POR_MODELO)
            ->get()
            ->map(fn (Convocatoria $c) => [
                'tipo' => 'convocatoria',
                'titulo' => (string) $c->title,
                'texto' => (string) $c->short_description,
                'url' => route('convocatorias.show', $c->slug),
                'fecha' => $c->closes_at?->format('d.m.Y'),
            ]);
    }

    private function buscarProyectos(string $q): Collection
    {
        return Project::query()
            ->published()
            ->select('id', 'title', 'slug', 'description', 'sort_order')
            ->where(fn ($x) => $x->where('title', 'like', "%{$q}%")
                                 ->orWhere('description', 'like', "%{$q}%"))
            ->orderBy('sort_order')
            ->limit(self::LIMITE_POR_MODELO)
            ->get()
            ->map(fn (Project $p) => [
                'tipo' => 'proyecto',
                'titulo' => (string) $p->title,
                // description guarda HTML saneado; aquí solo interesa el texto.
                'texto' => strip_tags((string) $p->description),
                'url' => route('projects.show', $p->slug),
                'fecha' => null,
            ]);
    }

    private function buscarPaginas(string $q): Collection
    {
        return Page::query()
            ->where('status', 'published')
            ->select('id', 'key', 'title', 'slug')
            ->where('title', 'like', "%{$q}%")
            ->orderBy('title')
            ->limit(self::LIMITE_POR_MODELO)
            ->get()
            ->map(fn (Page $p) => [
                'tipo' => 'pagina',
                'titulo' => (string) $p->title,
                'texto' => '',
                // La portada no cuelga de /{slug}: vive en la raíz.
                'url' => $p->key === 'home' ? route('home') : route('page.show', $p->slug),
                'fecha' => null,
            ]);
    }

    private function buscarFaqs(string $q): Collection
    {
        return Faq::query()
            ->active()
            ->select('id', 'question', 'answer')
            ->where(fn ($x) => $x->where('question', 'like', "%{$q}%")
                                 ->orWhere('answer', 'like', "%{$q}%"))
            ->orderBy('sort_order')
            ->limit(self::LIMITE_POR_MODELO)
            ->get()
            ->map(fn (Faq $f) => [
                'tipo' => 'faq',
                'titulo' => (string) $f->question,
                'texto' => strip_tags((string) $f->answer),
                // Las preguntas no tienen ficha propia: se abre el acordeón ya
                // filtrado por el mismo término.
                'url' => route('faq.index', ['q' => $q]),
                'fecha' => null,
            ]);
    }

    // ── Paginación ──────────────────────────────────────────────────────────

    /**
     * Pagina una colección ya fundida en memoria. No se puede delegar en la
     * base de datos porque los resultados vienen de cinco tablas sin relación
     * entre sí; el tope por modelo garantiza que el array nunca crece de más.
     */
    private function paginar(Collection $todos, Request $request): LengthAwarePaginator
    {
        $pagina = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $todos->forPage($pagina, self::POR_PAGINA)->values(),
            $todos->count(),
            self::POR_PAGINA,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    private function paginadorVacio(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, self::POR_PAGINA, 1, ['path' => $request->url()]);
    }

    // ── Presentación ────────────────────────────────────────────────────────

    /**
     * Devuelve HTML con las coincidencias envueltas en <mark>.
     *
     * SEGURIDAD -- La vista pinta esto con {!! !!}, así que el escapado tiene
     * que pasar AQUÍ y ANTES de insertar ninguna etiqueta: se escapan tanto el
     * texto como el término, y solo después se busca uno dentro del otro. Si se
     * resaltara primero y se escapara después, el <mark> se escaparía también;
     * y si no se escapara el término, cualquiera podría inyectar JavaScript en
     * la página con solo pasarlo por la URL.
     *
     * Escapar ambos lados mantiene la correspondencia: un término como "a&b"
     * queda "a&amp;b" en los dos, de modo que la coincidencia se sigue viendo.
     */
    public static function resaltar(string $texto, string $termino, int $limite = 200): string
    {
        $texto = trim(preg_replace('/\s+/u', ' ', $texto));

        // Se recorta alrededor de la primera coincidencia, no por el principio:
        // si la palabra buscada aparece en el párrafo cuarto, un Str::limit
        // desde el inicio devolvería un fragmento donde no se ve nada.
        $posicion = mb_stripos($texto, $termino);
        if ($posicion !== false && $posicion > 60) {
            $texto = '…' . mb_substr($texto, $posicion - 60);
        }
        $texto = Str::limit($texto, $limite);

        $escapado = e($texto);
        $terminoEscapado = e($termino);

        if ($terminoEscapado === '') {
            return $escapado;
        }

        // El <mark> sale SIN clases de Tailwind a propósito: tailwind.config.js
        // solo escanea resources/ y app/View/Components, no app/Http, así que
        // una clase escrita aquí nunca se compilaría y el resaltado saldría sin
        // estilo. Se estiliza el elemento `mark` en app.css.
        return preg_replace(
            '/' . preg_quote($terminoEscapado, '/') . '/iu',
            '<mark>$0</mark>',
            $escapado
        ) ?? $escapado;
    }

    /** Etiqueta legible de cada grupo de resultados. */
    public static function etiquetaTipo(string $tipo): string
    {
        return match ($tipo) {
            'noticia' => 'Noticias',
            'convocatoria' => 'Convocatorias',
            'proyecto' => 'Proyectos',
            'pagina' => 'Páginas',
            'faq' => 'Preguntas frecuentes',
            default => ucfirst($tipo),
        };
    }
}
