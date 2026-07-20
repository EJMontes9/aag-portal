<?php

namespace App\Console\Commands;

use App\Models\LotaipMonth;
use App\Models\LotaipYear;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Sincroniza la estructura de transparencia con el subdominio de documentos.
 *
 * POR QUE ESTE ENFOQUE
 * --------------------
 * Los documentos de la AAG viven en https://document.aag.org.ec, servidos por
 * un explorador propio. Al inspeccionarlo aparecen dos hechos que condicionan
 * la solucion:
 *
 *   1. Son mas de mil archivos (solo 2024 tiene 897).
 *   2. La estructura NO es uniforme: 2023 es plana (AÑO/Mes/archivo.pdf),
 *      mientras 2024 y 2025 anidan cuatro niveles
 *      (AÑO/Mes/Articulo 19/N. Literal/archivo.csv).
 *
 * Registrar cada archivo como una fila obligaria a mantener mil registros
 * sincronizados a mano y aplanaria una jerarquia que si tiene sentido para el
 * ciudadano. Por eso este comando NO copia archivos: enlaza cada MES con su
 * carpeta del explorador, que ya sabe presentarla.
 *
 * La consecuencia practica es la que interesa: cuando se suba un archivo nuevo
 * por FTP, aparece solo. No hay que volver a ejecutar nada.
 *
 * Si en algun momento se prefiere el listado dentro del portal (con nombre,
 * formato y peso en la linea grafica del sitio), se puede registrar documento
 * a documento desde Transparencia > Documentos; ambos modos conviven.
 */
class SincronizarDocumentosLotaip extends Command
{
    protected $signature = 'lotaip:sincronizar
                            {--seccion=lotaip : Seccion a sincronizar (lotaip o rendicion)}
                            {--dry-run : Muestra lo que haria sin tocar la base de datos}';

    protected $description = 'Enlaza los meses de transparencia con las carpetas del subdominio de documentos';

    /** Nombre de carpeta -> numero de mes. El explorador usa "01-Enero", "1-enero", "Enero"... */
    protected const MESES = [
        'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
        'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
        'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10,
        'noviembre' => 11, 'diciembre' => 12,
    ];

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
        $this->info("Seccion:    {$seccion}");
        if ($dryRun) {
            $this->warn('Modo simulacion: no se escribe nada en la base de datos.');
        }
        $this->newLine();

        $anios = $this->descubrirAnios($base);

        if (empty($anios)) {
            $this->error('No se encontro ninguna carpeta de año en el subdominio.');

            return self::FAILURE;
        }

        $this->line('Años encontrados: ' . implode(', ', $anios));
        $this->newLine();

        $totalMeses = 0;
        $totalArchivos = 0;

        foreach ($anios as $anio) {
            $carpetas = $this->descubrirMeses($base, $anio);

            if (empty($carpetas)) {
                $this->line("  {$anio}: sin archivos todavia");
                continue;
            }

            $registroAnio = $dryRun
                ? new LotaipYear(['year' => $anio, 'section' => $seccion])
                : LotaipYear::firstOrCreate(
                    ['year' => $anio, 'section' => $seccion],
                    ['is_active' => true]
                );

            $this->line("  {$anio}:");

            foreach ($carpetas as $carpeta => $numArchivos) {
                $mes = $this->numeroDeMes($carpeta);

                if (! $mes) {
                    $this->warn("    · carpeta no reconocida como mes: {$carpeta}");
                    continue;
                }

                // Ruta RELATIVA: el explorador la acepta, y asi el enlace no
                // depende de /home/<usuario>/... Si el hosting cambia, sigue
                // funcionando.
                $url = $base . '/?dir=' . rawurlencode($anio . '/' . $carpeta);

                $this->line(sprintf(
                    '    · %-12s %4d archivo%s  ->  ?dir=%s/%s',
                    LotaipMonth::MONTH_NAMES[$mes] ?? $carpeta,
                    $numArchivos,
                    $numArchivos === 1 ? ' ' : 's',
                    $anio,
                    $carpeta
                ));

                $totalMeses++;
                $totalArchivos += $numArchivos;

                if ($dryRun) {
                    continue;
                }

                LotaipMonth::updateOrCreate(
                    ['year_id' => $registroAnio->id, 'month' => $mes],
                    [
                        'mode'           => 'redirect',
                        'redirect_url'   => $url,
                        'redirect_label' => 'Ver documentos de ' . (LotaipMonth::MONTH_NAMES[$mes] ?? $carpeta),
                        'is_active'      => true,
                    ]
                );
            }
        }

        if (! $dryRun) {
            Cache::forget('transparency_tree');
            Cache::forget('transparency_tree_' . $seccion);
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d meses (%d archivos en total).',
            $dryRun ? 'Se enlazarian' : 'Enlazados',
            $totalMeses,
            $totalArchivos
        ));

        if ($dryRun) {
            $this->line('Vuelve a ejecutarlo sin --dry-run para aplicarlo.');
        }

        return self::SUCCESS;
    }

    /**
     * Años disponibles, leidos del menu lateral del explorador.
     */
    protected function descubrirAnios(string $base): array
    {
        $html = $this->pedir($base . '/');

        if ($html === null) {
            return [];
        }

        preg_match_all('/\?dir=([^\'"<>\s]+)/', $html, $m);

        $anios = [];
        foreach ($m[1] as $enlace) {
            $ruta = urldecode($enlace);
            // Del final de la ruta, quedarse con lo que sea un año de 4 cifras.
            $ultimo = basename(rtrim($ruta, '/'));
            if (preg_match('/^(19|20)\d{2}$/', $ultimo)) {
                $anios[$ultimo] = true;
            }
        }

        $anios = array_keys($anios);
        sort($anios);

        return $anios;
    }

    /**
     * Carpetas de mes de un año, con cuantos archivos cuelgan de cada una.
     *
     * Se deducen de los enlaces de descarga (?file=...) en vez de intentar
     * interpretar los acordeones del explorador: las rutas de archivo son
     * inequivocas y funcionan igual sea la estructura plana (2023) o anidada
     * en cuatro niveles (2024-2025).
     *
     * @return array<string,int> nombre de carpeta => numero de archivos
     */
    protected function descubrirMeses(string $base, string $anio): array
    {
        $html = $this->pedir($base . '/?dir=' . rawurlencode($anio));

        if ($html === null) {
            return [];
        }

        preg_match_all('/\?file=([^\'"<>\s]+)/', $html, $m);

        $carpetas = [];
        foreach ($m[1] as $enlace) {
            $ruta = urldecode($enlace);

            // La ruta puede venir absoluta (/home/.../2024/01-Enero/x.csv) o
            // relativa; en ambos casos interesa el segmento siguiente al año.
            $partes = array_values(array_filter(explode('/', $ruta), fn ($p) => $p !== ''));
            $pos = array_search($anio, $partes, true);

            if ($pos === false || ! isset($partes[$pos + 1])) {
                continue;
            }

            $carpetaMes = $partes[$pos + 1];
            $carpetas[$carpetaMes] = ($carpetas[$carpetaMes] ?? 0) + 1;
        }

        // Ordenar por numero de mes, no alfabeticamente
        uksort($carpetas, fn ($a, $b) => ($this->numeroDeMes($a) ?? 99) <=> ($this->numeroDeMes($b) ?? 99));

        return $carpetas;
    }

    /**
     * "01-Enero" -> 1 ; "Diciembre" -> 12 ; "Excel" -> null
     */
    protected function numeroDeMes(string $carpeta): ?int
    {
        $nombre = strtolower(trim($carpeta));
        $nombre = preg_replace('/^\d+\s*[-_.]\s*/', '', $nombre); // quita "01-"
        $nombre = str_replace(['_', '-'], ' ', $nombre);
        $nombre = trim($nombre);

        // Sin tildes, para que "Septiembre" y "septiembre" coincidan igual
        $nombre = strtr($nombre, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);

        foreach (self::MESES as $mes => $numero) {
            if (str_contains($nombre, $mes)) {
                return $numero;
            }
        }

        return null;
    }

    protected function pedir(string $url): ?string
    {
        try {
            $resp = Http::timeout(30)->retry(2, 500)->get($url);

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
