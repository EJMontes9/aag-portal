<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Trae al portal el archivo histórico de noticias del WordPress anterior.
 *
 * QUÉ HACE Y POR QUÉ
 * ------------------
 * El portal anterior publica su contenido en la API REST de WordPress
 * (/wp-json/wp/v2/posts), que devuelve el título, el cuerpo, el extracto, la
 * fecha, la categoría y la imagen destacada de cada entrada. Este comando lee
 * esa API y crea la noticia equivalente en el portal nuevo.
 *
 * Se lee de la API y no de la base de datos de WordPress a propósito: no hace
 * falta credencial alguna, no se toca el sitio anterior y el resultado es el
 * mismo HTML que ya se está publicando, sin los residuos de la tabla wp_posts
 * (revisiones, borradores, autoguardados).
 *
 * IDEMPOTENTE
 * -----------
 * La correspondencia se establece por slug. Volver a ejecutarlo no duplica
 * nada: actualiza lo que ya existe y crea solo lo que falta. Si se interrumpe a
 * mitad, se relanza y continúa.
 *
 * IMÁGENES
 * --------
 * La imagen destacada se descarga al disco público del portal, no se enlaza al
 * dominio anterior. Es deliberado: cuando el WordPress se apague, un enlace
 * remoto dejaría cada noticia sin foto.
 *
 * USO
 * ---
 *   php artisan noticias:importar-wordpress --dry-run     ver que haria
 *   php artisan noticias:importar-wordpress               importarlo todo
 *   php artisan noticias:importar-wordpress --desde=2023-01-01
 *   php artisan noticias:importar-wordpress --categoria="Noticias AAG"
 *   php artisan noticias:importar-wordpress --sin-imagenes
 */
class ImportarNoticiasWordpress extends Command
{
    protected $signature = 'noticias:importar-wordpress
                            {--origen=https://www.aag.org.ec : Dominio del portal WordPress}
                            {--desde= : Importar solo lo publicado desde esta fecha (AAAA-MM-DD)}
                            {--categoria=* : Importar solo estas categorías de WordPress}
                            {--sin-imagenes : No descargar las imágenes destacadas}
                            {--borrador : Crear las noticias como borrador en vez de publicadas}
                            {--dry-run : Muestra lo que haría sin tocar la base de datos}';

    protected $description = 'Importa el archivo histórico de noticias desde el WordPress anterior';

    /** Paginado de la API de WordPress: es su máximo por petición. */
    protected const POR_PAGINA = 100;

    /** Tope de páginas, por si la API devolviera algo inesperado. */
    protected const MAX_PAGINAS = 50;

    public function handle(): int
    {
        $origen      = rtrim((string) $this->option('origen'), '/');
        $dryRun      = (bool) $this->option('dry-run');
        $sinImagenes = (bool) $this->option('sin-imagenes');
        $estado      = $this->option('borrador') ? 'draft' : 'published';
        $desde       = $this->option('desde') ? strtotime((string) $this->option('desde')) : null;
        $categorias  = array_map(fn ($c) => mb_strtolower(trim($c)), (array) $this->option('categoria'));

        if ($this->option('desde') && ! $desde) {
            $this->error('La fecha de --desde no es válida. Usa el formato AAAA-MM-DD.');

            return self::FAILURE;
        }

        $this->info("Origen: {$origen}");
        if ($dryRun) {
            $this->warn('Modo simulacion: no se escribe nada.');
        }
        $this->newLine();

        $entradas = $this->descargarEntradas($origen);

        if ($entradas === null) {
            return self::FAILURE;
        }

        if (empty($entradas)) {
            $this->error('La API no devolvió ninguna entrada.');

            return self::FAILURE;
        }

        $this->info(sprintf('Recibidas %d entradas del portal anterior.', count($entradas)));
        $this->newLine();

        $totales = ['nuevas' => 0, 'actualizadas' => 0, 'omitidas' => 0, 'imagenes' => 0, 'fallos' => 0];

        foreach ($entradas as $entrada) {
            $titulo = $this->texto($entrada['title']['rendered'] ?? '');
            $slug   = (string) ($entrada['slug'] ?? '');

            if ($titulo === '' || $slug === '') {
                $totales['omitidas']++;
                continue;
            }

            $fecha = strtotime((string) ($entrada['date'] ?? ''));
            if (! $fecha) {
                $fecha = time();
            }

            if ($desde && $fecha < $desde) {
                $totales['omitidas']++;
                continue;
            }

            $nombreCategoria = $this->categoriaDe($entrada);

            if ($categorias && ! in_array(mb_strtolower($nombreCategoria), $categorias, true)) {
                $totales['omitidas']++;
                continue;
            }

            $existente = News::where('slug', $slug)->first();

            if ($dryRun) {
                $this->line(sprintf(
                    '  %s  %s  <fg=gray>[%s]</>',
                    $existente ? '~' : '+',
                    Str::limit($titulo, 70),
                    date('Y-m-d', $fecha)
                ));
                $existente ? $totales['actualizadas']++ : $totales['nuevas']++;
                continue;
            }

            $categoria = $this->categoria($nombreCategoria);

            $portada = null;
            if (! $sinImagenes) {
                $portada = $this->descargarPortada($entrada, $slug);
                if ($portada) {
                    $totales['imagenes']++;
                }
            }

            $datos = [
                'title'            => $titulo,
                'category_id'      => $categoria?->id,
                'excerpt'          => Str::limit($this->texto($entrada['excerpt']['rendered'] ?? ''), 400, ''),
                'content'          => (string) ($entrada['content']['rendered'] ?? ''),
                'status'           => $estado,
                'published_at'     => date('Y-m-d H:i:s', $fecha),
                'featured_on_home' => false,
                'meta_title'       => Str::limit($titulo, 60, ''),
                'meta_description' => Str::limit($this->texto($entrada['excerpt']['rendered'] ?? ''), 160, ''),
            ];

            // La portada solo se sobrescribe si se ha descargado ahora. Asi una
            // segunda pasada no borra una imagen puesta a mano desde el panel.
            if ($portada) {
                $datos['cover_image']     = $portada;
                $datos['cover_image_alt'] = $titulo;
            }

            try {
                News::updateOrCreate(['slug' => $slug], $datos);
                $existente ? $totales['actualizadas']++ : $totales['nuevas']++;

                $this->line(sprintf(
                    '  %s  %s',
                    $existente ? '<fg=yellow>~</>' : '<fg=green>+</>',
                    Str::limit($titulo, 80)
                ));
            } catch (\Throwable $e) {
                $totales['fallos']++;
                $this->warn(sprintf('  ! %s -> %s', Str::limit($titulo, 60), $e->getMessage()));
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d noticias nuevas y %d actualizadas. %d omitidas por los filtros.',
            $dryRun ? 'Se crearian' : 'Importadas:',
            $totales['nuevas'],
            $totales['actualizadas'],
            $totales['omitidas']
        ));

        if (! $dryRun) {
            $this->line(sprintf('Imágenes de portada descargadas: %d', $totales['imagenes']));
        }

        if ($totales['fallos'] > 0) {
            $this->warn(sprintf('Fallaron %d entradas. Revisa los avisos anteriores.', $totales['fallos']));
        }

        return self::SUCCESS;
    }

    /**
     * Recorre el paginado de la API hasta agotarlo.
     *
     * @return array<int,array>|null  null si la API no responde
     */
    protected function descargarEntradas(string $origen): ?array
    {
        $entradas = [];

        for ($pagina = 1; $pagina <= self::MAX_PAGINAS; $pagina++) {
            $url = sprintf('%s/wp-json/wp/v2/posts?per_page=%d&page=%d&_embed', $origen, self::POR_PAGINA, $pagina);

            try {
                $resp = Http::timeout(180)->retry(2, 1000)->get($url);
            } catch (\Throwable $e) {
                $this->error("No se pudo consultar la API del portal anterior: {$e->getMessage()}");

                return $pagina === 1 ? null : $entradas;
            }

            // Al pasarse del último índice, WordPress responde 400. Es el final
            // del recorrido, no un error.
            if ($resp->status() === 400) {
                break;
            }

            if (! $resp->successful()) {
                $this->error("La API respondió HTTP {$resp->status()} en la página {$pagina}.");

                return $pagina === 1 ? null : $entradas;
            }

            $lote = $resp->json();

            if (! is_array($lote) || empty($lote)) {
                break;
            }

            $entradas = array_merge($entradas, $lote);
            $this->line(sprintf('  página %d: %d entradas', $pagina, count($lote)));

            if (count($lote) < self::POR_PAGINA) {
                break;
            }
        }

        return $entradas;
    }

    /**
     * Descarga la imagen destacada al disco público y devuelve su ruta
     * relativa, que es lo que el modelo espera en cover_image.
     */
    protected function descargarPortada(array $entrada, string $slug): ?string
    {
        $url = $entrada['_embedded']['wp:featuredmedia'][0]['source_url'] ?? null;

        if (! is_string($url) || $url === '') {
            return null;
        }

        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));

        // WordPress sirve .jpg.webp y similares; nos quedamos con algo que el
        // navegador entienda y descartamos cualquier extensión rara.
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $extension = 'jpg';
        }

        $destino = 'noticias/' . $slug . '.' . $extension;

        if (Storage::disk('public')->exists($destino)) {
            return $destino;
        }

        try {
            $resp = Http::timeout(120)->retry(2, 1000)->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $resp->successful()) {
            return null;
        }

        $cuerpo = $resp->body();

        if (strlen($cuerpo) < 512) {
            return null;
        }

        Storage::disk('public')->put($destino, $cuerpo);

        return $destino;
    }

    protected function categoriaDe(array $entrada): string
    {
        $terminos = $entrada['_embedded']['wp:term'][0] ?? [];

        foreach ($terminos as $termino) {
            if (! empty($termino['name'])) {
                return $this->texto($termino['name']);
            }
        }

        return 'Noticias';
    }

    /** Cache en memoria para no consultar la misma categoría en cada vuelta. */
    protected array $categoriasCache = [];

    protected function categoria(string $nombre): ?NewsCategory
    {
        $slug = Str::slug($nombre);

        if ($slug === '') {
            return null;
        }

        if (! isset($this->categoriasCache[$slug])) {
            $this->categoriasCache[$slug] = NewsCategory::firstOrCreate(
                ['slug' => $slug],
                ['name' => $nombre]
            );
        }

        return $this->categoriasCache[$slug];
    }

    /** HTML del WordPress -> texto plano legible. */
    protected function texto(string $html): string
    {
        $texto = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = preg_replace('/\s+/u', ' ', $texto);

        return trim($texto);
    }
}
