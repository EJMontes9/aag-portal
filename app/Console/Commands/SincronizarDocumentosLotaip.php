<?php

namespace App\Console\Commands;

use App\Models\LotaipDocument;
use App\Models\LotaipMonth;
use App\Models\LotaipYear;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Trae al portal los documentos de transparencia alojados en el subdominio.
 *
 * QUÉ HACE Y POR QUÉ
 * ------------------
 * Los archivos se suben por FTP a https://document.aag.org.ec y NO se mueven de
 * ahí: sus direcciones están publicadas y enlazadas desde documentación
 * anterior. Este comando no copia nada; solo REGISTRA cada archivo en la base
 * de datos con su ruta, de modo que el portal pueda listarlos con su propio
 * diseño en vez de mandar al ciudadano al explorador del subdominio.
 *
 * La descarga apunta a la URL directa del archivo
 * (https://document.aag.org.ec/2024/01-Enero/.../Metadatos.csv), no al script
 * "?file=" del explorador: es una dirección limpia, no depende de la ruta
 * interna del servidor (/home/<usuario>/...) y sobrevive a un cambio de
 * hosting.
 *
 * ESTRUCTURA
 * ----------
 * No es uniforme, y el comando lo contempla:
 *
 *   2023  ->  AÑO/Mes/archivo.pdf                       (plana)
 *   2024  ->  AÑO/Mes/Articulo 19/N. Literal/archivo.csv (anidada)
 *
 * Lo que hay entre el mes y el archivo se guarda como "literal", que agrupa los
 * documentos en la página igual que están en el servidor. Si no hay nada
 * intermedio (2023), el documento queda suelto en su mes.
 *
 * USO
 * ---
 *   php artisan lotaip:sincronizar --dry-run   ver qué haría
 *   php artisan lotaip:sincronizar             aplicarlo
 *
 * Es idempotente y se ejecuta cada madrugada desde routes/console.php, así que
 * subir por FTP es lo único que hay que hacer: al día siguiente está publicado.
 */
class SincronizarDocumentosLotaip extends Command
{
    protected $signature = 'lotaip:sincronizar
                            {--seccion=lotaip : Sección a sincronizar (lotaip o rendicion)}
                            {--dry-run : Muestra lo que haría sin tocar la base de datos}';

    protected $description = 'Registra en el portal los documentos alojados en el subdominio';

    protected const MESES = [
        'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
        'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
        'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10,
        'noviembre' => 11, 'diciembre' => 12,
    ];

    /**
     * Carpetas intermedias que no aportan como agrupador: son un nivel
     * organizativo del servidor, no un literal de la LOTAIP.
     */
    protected const CARPETAS_IGNORADAS = ['articulo 19', 'article 19', 'art 19', 'excel', 'varios'];

    /**
     * Variantes de nombre con que puede aparecer la carpeta de un mes.
     * El criterio ha ido cambiando: 2023 y 2024 usan "01-Enero"; 2026, "Enero".
     */
    protected function variantesDeCarpeta(int $mes): array
    {
        $nombre = LotaipMonth::MONTH_NAMES[$mes];

        return [
            $nombre,                                  // Enero
            sprintf('%02d-%s', $mes, $nombre),        // 01-Enero
            sprintf('%d-%s', $mes, $nombre),          // 1-Enero
            sprintf('%02d_%s', $mes, $nombre),        // 01_Enero
            sprintf('%02d %s', $mes, $nombre),        // 01 Enero
        ];
    }

    /**
     * Busca el config_link.txt de un mes.
     *
     * Es el mecanismo del propio explorador: si una carpeta contiene un
     * archivo config_link.txt, en vez de listar su contenido muestra un enlace.
     * La AAG lo usa desde 2025 para remitir al portal nacional de transparencia
     * de la Defensoría del Pueblo, donde se publica ahora la información; los
     * archivos se siguen subiendo por detrás pero no se muestran.
     *
     * Formato del archivo:  URL|Texto del enlace
     * (el texto es opcional; si falta, se usa la propia URL)
     *
     * @return array{url:string,label:string}|null
     */
    protected function buscarEnlaceDeMes(string $base, string $anio, int $mes, ?string $carpetaConocida = null): ?array
    {
        // Si ya se sabe cómo se llama la carpeta (porque tiene archivos), se
        // prueba solo esa; si no, se tantean las variantes conocidas.
        $candidatas = $carpetaConocida ? [$carpetaConocida] : $this->variantesDeCarpeta($mes);

        foreach ($candidatas as $carpeta) {
            $url = $base . '/' . rawurlencode($anio) . '/' . rawurlencode($carpeta) . '/config_link.txt';

            try {
                $resp = Http::timeout(15)->get($url);
            } catch (\Throwable) {
                continue;
            }

            if (! $resp->successful()) {
                continue;
            }

            $contenido = trim($resp->body());
            if ($contenido === '') {
                continue;
            }

            $partes  = explode('|', $contenido);
            $destino = trim($partes[0]);
            $texto   = isset($partes[1]) ? trim($partes[1]) : '';

            // Solo http/https: el contenido viene de un archivo del servidor,
            // pero acaba siendo un enlace que pulsa el ciudadano.
            $esquema = strtolower((string) parse_url($destino, PHP_URL_SCHEME));
            if (! in_array($esquema, ['http', 'https'], true)) {
                $this->warn("    config_link.txt de {$anio}/{$carpeta} ignorado: esquema no permitido");
                continue;
            }

            return [
                'url'   => $destino,
                'label' => $texto !== '' ? $texto : 'Ver documentos',
            ];
        }

        return null;
    }

    public function handle(): int
    {
        $seccion = $this->option('seccion');
        $dryRun  = (bool) $this->option('dry-run');

        $base = rtrim((string) settings('documents_base_url', ''), '/');

        if ($base === '') {
            $this->error('No hay subdominio configurado.');
            $this->line('Configúralo en el panel: Ajustes del sitio > Documentos.');

            return self::FAILURE;
        }

        $this->info("Subdominio: {$base}");
        if ($dryRun) {
            $this->warn('Modo simulación: no se escribe nada.');
        }
        $this->newLine();

        $anios = $this->descubrirAnios($base);

        if (empty($anios)) {
            $this->error('No se encontró ninguna carpeta de año.');

            return self::FAILURE;
        }

        $totales = ['anios' => 0, 'meses' => 0, 'docs' => 0, 'nuevos' => 0, 'enlaces' => 0, 'ocultos' => 0];

        foreach ($anios as $anio) {
            // OJO: un año sin archivos NO se descarta. Desde 2025 hay meses que
            // solo tienen un config_link.txt remitiendo a otro portal, sin
            // ningún archivo listado; 2026 es así entero.
            $archivos = $this->descubrirArchivos($base, $anio);

            $totales['anios']++;
            $this->line("  <fg=cyan>{$anio}</>");

            $registroAnio = $dryRun
                ? new LotaipYear(['id' => 0, 'year' => $anio, 'section' => $seccion])
                : LotaipYear::firstOrCreate(
                    ['year' => $anio, 'section' => $seccion],
                    ['is_active' => true]
                );

            // Agrupar por mes
            $porMes = [];
            $carpetaDeMes = [];
            foreach ($archivos as $ruta) {
                $info = $this->analizarRuta($ruta, $anio);
                if ($info) {
                    $porMes[$info['mes']][] = $info;
                    $carpetaDeMes[$info['mes']] = $info['carpeta'];
                }
            }

            // Meses que NO tienen archivos: pueden tener un config_link.txt que
            // remite a otro portal. Es el caso de 2025 y 2026, donde la
            // información pasó a publicarse en el portal de la Defensoría.
            foreach (range(1, 12) as $m) {
                if (! isset($porMes[$m])) {
                    $porMes[$m] = [];
                }
            }
            ksort($porMes);

            foreach ($porMes as $numeroMes => $documentos) {
                $nombreMes = LotaipMonth::MONTH_NAMES[$numeroMes] ?? "Mes {$numeroMes}";

                // Con archivos -> se listan en el portal.
                // Sin archivos -> se mira si hay enlace de redireccion.
                $enlace = empty($documentos)
                    ? $this->buscarEnlaceDeMes($base, $anio, $numeroMes)
                    : null;

                if (empty($documentos) && ! $enlace) {
                    // El mes no tiene nada en el servidor: ni archivos ni
                    // config_link.txt. Se OCULTA en vez de dejarlo como "Sin
                    // documentos", que es lo que hacía antes.
                    //
                    // El seeder inicial creó los doce meses de cada año, así
                    // que sin esto el portal anuncia meses que ni siquiera
                    // existen como carpeta (Julio a Diciembre de 2026).
                    //
                    // Solo se ocultan los que están VACÍOS de verdad: si el mes
                    // tiene documentos cargados a mano desde el panel, se
                    // respeta y no se toca.
                    if (! $dryRun) {
                        $mesExistente = LotaipMonth::where('year_id', $registroAnio->id)
                            ->where('month', $numeroMes)
                            ->withCount('documents')
                            ->first();

                        if ($mesExistente && $mesExistente->documents_count === 0 && $mesExistente->is_active) {
                            $mesExistente->update(['is_active' => false]);
                            $totales['ocultos']++;
                        }
                    } else {
                        $totales['ocultos']++;
                    }

                    continue;
                }

                $totales['meses']++;

                if ($enlace) {
                    $this->line(sprintf('    %-12s  →  %s', $nombreMes, $enlace['label']));
                    $totales['enlaces']++;

                    if (! $dryRun) {
                        LotaipMonth::updateOrCreate(
                            ['year_id' => $registroAnio->id, 'month' => $numeroMes],
                            [
                                'mode'           => 'redirect',
                                'redirect_url'   => $enlace['url'],
                                'redirect_label' => $enlace['label'],
                                'is_active'      => true,
                            ]
                        );
                    }

                    continue;
                }

                $this->line(sprintf('    %-12s %3d documentos', $nombreMes, count($documentos)));

                if ($dryRun) {
                    $totales['docs'] += count($documentos);
                    // Muestra un par de ejemplos para poder revisar el resultado
                    foreach (array_slice($documentos, 0, 2) as $d) {
                        $this->line(sprintf(
                            '                 · %s%s',
                            $d['literal'] ? "[{$d['literal']}] " : '',
                            $d['titulo']
                        ));
                    }
                    continue;
                }

                $registroMes = LotaipMonth::updateOrCreate(
                    ['year_id' => $registroAnio->id, 'month' => $numeroMes],
                    ['mode' => 'files', 'is_active' => true]
                );

                foreach ($documentos as $orden => $doc) {
                    $existente = LotaipDocument::where('month_id', $registroMes->id)
                        ->where('file_path', $doc['ruta'])
                        ->first();

                    if (! $existente) {
                        $totales['nuevos']++;
                    }

                    LotaipDocument::updateOrCreate(
                        ['month_id' => $registroMes->id, 'file_path' => $doc['ruta']],
                        [
                            'source'     => LotaipDocument::SOURCE_EXTERNAL,
                            'title'      => $doc['titulo'],
                            'literal'    => $doc['literal'],
                            'extension'  => $doc['extension'],
                            'is_active'  => true,
                            'sort_order' => $orden,
                        ]
                    );

                    $totales['docs']++;
                }
            }
        }

        if (! $dryRun) {
            Cache::forget('transparency_tree');
            Cache::forget('transparency_tree_' . $seccion);
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d documentos y %d meses con enlace, en %d meses de %d años.%s',
            $dryRun ? 'Se registrarían' : 'Registrados',
            $totales['docs'],
            $totales['enlaces'],
            $totales['meses'],
            $totales['anios'],
            (! $dryRun && $totales['nuevos'] > 0) ? " ({$totales['nuevos']} documentos nuevos)" : ''
        ));

        if ($totales['ocultos'] > 0) {
            $this->line(sprintf(
                '%s %d meses sin contenido en el servidor.',
                $dryRun ? 'Se ocultarían' : 'Ocultados',
                $totales['ocultos']
            ));
        }

        return self::SUCCESS;
    }

    /**
     * Descompone una ruta del subdominio en los datos del documento.
     *
     * "2024/01-Enero/Articulo 19/9. Listado de empresas/Metadatos.csv"
     *   -> mes 1, literal "9. Listado de empresas", titulo "Metadatos"
     */
    protected function analizarRuta(string $ruta, string $anio): ?array
    {
        $partes = array_values(array_filter(explode('/', $ruta), fn ($p) => $p !== ''));

        $pos = array_search($anio, $partes, true);
        if ($pos === false || ! isset($partes[$pos + 1])) {
            return null;
        }

        $mes = $this->numeroDeMes($partes[$pos + 1]);
        if (! $mes) {
            return null;
        }

        $archivo = end($partes);
        $intermedias = array_slice($partes, $pos + 2, -1);

        // El agrupador es la última carpeta con significado (se descartan los
        // niveles meramente organizativos, como "Articulo 19").
        $literal = null;
        foreach (array_reverse($intermedias) as $carpeta) {
            if (! in_array($this->normalizar($carpeta), self::CARPETAS_IGNORADAS, true)) {
                $literal = $carpeta;
                break;
            }
        }

        return [
            // Ruta RELATIVA al subdominio: el modelo la convierte en URL
            // directa y escapa cada segmento (hay espacios y tildes de sobra).
            'ruta'      => implode('/', array_slice($partes, $pos)),
            'titulo'    => $this->titulo($archivo),
            'literal'   => $literal ? mb_substr($literal, 0, 255) : null,
            'extension' => strtolower(pathinfo($archivo, PATHINFO_EXTENSION)),
            'mes'       => $mes,
            // Nombre real de la carpeta ("01-Enero" o "Enero" según el año),
            // para poder pedir su config_link.txt sin tantear variantes.
            'carpeta'   => $partes[$pos + 1],
        ];
    }

    /**
     * Nombre de archivo -> título legible.
     * "Literal_a4_-_Metas y objetivos.pdf" -> "Literal a4 - Metas y objetivos"
     */
    protected function titulo(string $archivo): string
    {
        $nombre = pathinfo($archivo, PATHINFO_FILENAME);
        $nombre = str_replace(['_', '+'], ' ', $nombre);
        $nombre = preg_replace('/\s+/', ' ', $nombre);

        return trim($nombre) ?: $archivo;
    }

    protected function numeroDeMes(string $carpeta): ?int
    {
        $nombre = $this->normalizar($carpeta);
        $nombre = preg_replace('/^\d+\s*[-_.]\s*/', '', $nombre);

        foreach (self::MESES as $mes => $numero) {
            if (str_contains($nombre, $mes)) {
                return $numero;
            }
        }

        return null;
    }

    protected function normalizar(string $texto): string
    {
        $t = mb_strtolower(trim($texto));
        $t = strtr($t, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);

        return trim(str_replace(['_', '-', '.'], ' ', $t));
    }

    protected function descubrirAnios(string $base): array
    {
        $html = $this->pedir($base . '/');
        if ($html === null) {
            return [];
        }

        preg_match_all('/\?dir=([^\'"<>\s]+)/', $html, $m);

        $anios = [];
        foreach ($m[1] as $enlace) {
            $ultimo = basename(rtrim(urldecode($enlace), '/'));
            if (preg_match('/^(19|20)\d{2}$/', $ultimo)) {
                $anios[$ultimo] = true;
            }
        }

        $anios = array_keys($anios);
        sort($anios);

        return $anios;
    }

    /**
     * Rutas de todos los archivos de un año, leídas de los enlaces de descarga.
     *
     * @return string[]
     */
    protected function descubrirArchivos(string $base, string $anio): array
    {
        $html = $this->pedir($base . '/?dir=' . rawurlencode($anio));
        if ($html === null) {
            return [];
        }

        preg_match_all('/\?file=([^\'"<>\s]+)/', $html, $m);

        $rutas = [];
        foreach ($m[1] as $enlace) {
            $rutas[] = urldecode($enlace);
        }

        sort($rutas, SORT_NATURAL | SORT_FLAG_CASE);

        return $rutas;
    }

    protected function pedir(string $url): ?string
    {
        try {
            $resp = Http::timeout(60)->retry(2, 500)->get($url);

            if (! $resp->successful()) {
                $this->warn("  No se pudo leer {$url} (HTTP {$resp->status()})");

                return null;
            }

            return $resp->body();
        } catch (\Throwable $e) {
            $this->warn("  Error al leer {$url}: " . $e->getMessage());

            return null;
        }
    }
}
