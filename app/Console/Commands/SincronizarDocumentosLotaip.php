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
 * QUE HACE Y POR QUE
 * ------------------
 * Los archivos se suben por FTP a https://document.aag.org.ec y NO se mueven de
 * ahi: sus direcciones estan publicadas y enlazadas desde documentacion
 * anterior. Este comando no copia nada; solo REGISTRA cada archivo en la base
 * de datos con su ruta, de modo que el portal pueda listarlos con su propio
 * diseño en vez de mandar al ciudadano al explorador del subdominio.
 *
 * La descarga apunta a la URL directa del archivo
 * (https://document.aag.org.ec/2024/01-Enero/.../Metadatos.csv), no al script
 * "?file=" del explorador: es una direccion limpia, no depende de la ruta
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
 * documentos en la pagina igual que estan en el servidor. Si no hay nada
 * intermedio (2023), el documento queda suelto en su mes.
 *
 * USO
 * ---
 *   php artisan lotaip:sincronizar --dry-run   ver que haria
 *   php artisan lotaip:sincronizar             aplicarlo
 *
 * Es idempotente y se ejecuta cada madrugada desde routes/console.php, asi que
 * subir por FTP es lo unico que hay que hacer: al dia siguiente esta publicado.
 */
class SincronizarDocumentosLotaip extends Command
{
    protected $signature = 'lotaip:sincronizar
                            {--seccion=lotaip : Seccion a sincronizar (lotaip o rendicion)}
                            {--dry-run : Muestra lo que haria sin tocar la base de datos}';

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

    public function handle(): int
    {
        $seccion = $this->option('seccion');
        $dryRun  = (bool) $this->option('dry-run');

        $base = rtrim((string) settings('documents_base_url', ''), '/');

        if ($base === '') {
            $this->error('No hay subdominio configurado.');
            $this->line('Configuralo en el panel: Ajustes del sitio > Documentos.');

            return self::FAILURE;
        }

        $this->info("Subdominio: {$base}");
        if ($dryRun) {
            $this->warn('Modo simulacion: no se escribe nada.');
        }
        $this->newLine();

        $anios = $this->descubrirAnios($base);

        if (empty($anios)) {
            $this->error('No se encontro ninguna carpeta de año.');

            return self::FAILURE;
        }

        $totales = ['anios' => 0, 'meses' => 0, 'docs' => 0, 'nuevos' => 0];

        foreach ($anios as $anio) {
            $archivos = $this->descubrirArchivos($base, $anio);

            if (empty($archivos)) {
                $this->line("  {$anio}: sin archivos todavia");
                continue;
            }

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
            foreach ($archivos as $ruta) {
                $info = $this->analizarRuta($ruta, $anio);
                if ($info) {
                    $porMes[$info['mes']][] = $info;
                }
            }
            ksort($porMes);

            foreach ($porMes as $numeroMes => $documentos) {
                $totales['meses']++;
                $nombreMes = LotaipMonth::MONTH_NAMES[$numeroMes] ?? "Mes {$numeroMes}";

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
            '%s %d documentos en %d meses de %d años.%s',
            $dryRun ? 'Se registrarian' : 'Registrados',
            $totales['docs'],
            $totales['meses'],
            $totales['anios'],
            (! $dryRun && $totales['nuevos'] > 0) ? " ({$totales['nuevos']} nuevos)" : ''
        ));

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

        // El agrupador es la ultima carpeta con significado (se descartan los
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
        ];
    }

    /**
     * Nombre de archivo -> titulo legible.
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
     * Rutas de todos los archivos de un año, leidas de los enlaces de descarga.
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
