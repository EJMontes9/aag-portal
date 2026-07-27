<?php

namespace App\Console\Commands;

use App\Models\Convocatoria;
use App\Models\News;
use App\Models\Project;
use Illuminate\Console\Command;

/**
 * Retira del portal el contenido de muestra que se cargo durante el desarrollo.
 *
 * POR QUE EXISTE
 * --------------
 * Para poder enseñar el portal antes de tener contenido real se sembraron unas
 * cuantas noticias, proyectos y convocatorias inventadas. Sirven mientras se
 * construye, pero no pueden quedar publicadas: describen obras que no existen y
 * cifras que nadie ha aprobado, y el ciudadano no tiene forma de distinguirlas
 * del contenido legitimo.
 *
 * POR QUE NO BORRA A LA PRIMERA
 * -----------------------------
 * La correspondencia se hace por slug, y un slug puede haberse reutilizado para
 * contenido real (por ejemplo, si la obra de muestra acabo existiendo de
 * verdad). Por eso el comando enseña primero lo que encontro y no borra nada
 * hasta que se le pasa --confirmar. Borrar es irreversible: los modelos del
 * portal no tienen borrado logico.
 *
 * USO
 * ---
 *   php artisan contenido:limpiar-ejemplo               ver que borraria
 *   php artisan contenido:limpiar-ejemplo --confirmar   borrarlo
 */
class LimpiarContenidoEjemplo extends Command
{
    protected $signature = 'contenido:limpiar-ejemplo
                            {--confirmar : Borra de verdad. Sin esta opcion solo muestra lo que haria}
                            {--sin-convocatorias : No tocar las convocatorias}';

    protected $description = 'Retira las noticias, proyectos y convocatorias de muestra del desarrollo';

    /**
     * Contenido sembrado durante el desarrollo, identificado por su slug.
     * Verificado contra el portal en produccion el 27 de julio de 2026.
     */
    protected const NOTICIAS = [
        'aag-presenta-su-plan-operativo-anual-2026',
        'aeropuerto-jose-joaquin-de-olmedo-recibe-certificacion-ambiental',
        'convocatoria-abierta-12-nuevos-puestos-en-areas-tecnicas',
        'foro-de-aviacion-sostenible-reunira-a-expertos-en-guayaquil',
        'resultados-del-primer-trimestre-crecimiento-del-8-en-pasajeros',
    ];

    protected const PROYECTOS = [
        'centro-de-capacitacion-aag',
        'modernizacion-de-pistas-y-plataformas',
        'nueva-terminal-de-carga-aerea',
        'renovacion-de-salas-vip',
        'sistema-solar-fotovoltaico',
    ];

    protected const CONVOCATORIAS = [
        'jefe-operaciones-aeroportuarias',
    ];

    public function handle(): int
    {
        $confirmar = (bool) $this->option('confirmar');

        $grupos = [
            ['titulo' => 'Noticias',  'modelo' => News::class,    'slugs' => self::NOTICIAS],
            ['titulo' => 'Proyectos', 'modelo' => Project::class, 'slugs' => self::PROYECTOS],
        ];

        if (! $this->option('sin-convocatorias')) {
            $grupos[] = ['titulo' => 'Convocatorias', 'modelo' => Convocatoria::class, 'slugs' => self::CONVOCATORIAS];
        }

        if (! $confirmar) {
            $this->warn('Modo revision: no se borra nada. Añade --confirmar para aplicarlo.');
            $this->newLine();
        }

        $total = 0;
        $borrados = 0;

        foreach ($grupos as $grupo) {
            /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelo */
            $modelo = $grupo['modelo'];

            $registros = $modelo::whereIn('slug', $grupo['slugs'])->get();

            $this->line("<fg=cyan>{$grupo['titulo']}</> — encontrados: {$registros->count()} de " . count($grupo['slugs']));

            foreach ($registros as $registro) {
                $total++;
                $this->line(sprintf('    · %s  <fg=gray>(%s)</>', $registro->title ?? $registro->slug, $registro->slug));

                if ($confirmar) {
                    try {
                        $registro->delete();
                        $borrados++;
                    } catch (\Throwable $e) {
                        $this->warn("      ! no se pudo borrar: {$e->getMessage()}");
                    }
                }
            }

            $faltantes = array_diff($grupo['slugs'], $registros->pluck('slug')->all());
            foreach ($faltantes as $slug) {
                $this->line("    <fg=gray>· {$slug} — ya no esta</>");
            }

            $this->newLine();
        }

        if (! $confirmar) {
            $this->info("Se borrarian {$total} registros. Ejecuta de nuevo con --confirmar para aplicarlo.");

            return self::SUCCESS;
        }

        $this->info("Borrados {$borrados} de {$total} registros de muestra.");

        if ($borrados > 0) {
            $this->line('Revisa la portada: si algun bloque destacaba uno de estos contenidos, quedara vacio.');
        }

        return self::SUCCESS;
    }
}
