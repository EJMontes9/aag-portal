<?php

namespace App\Console\Commands;

use App\Models\Convocatoria;
use App\Models\Faq;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Corrige tildes/eñes que se perdieron en el contenido sembrado (seeders) y
 * que nunca se arreglaron en la base de datos, aunque el código fuente ya
 * tenía la corrección. Es un fix de datos, de una sola vez: reemplaza
 * palabras completas (con límite de palabra) por su forma acentuada
 * correcta, sin tocar nada que ya esté bien escrito.
 *
 * Uso: php artisan app:fix-missing-accents
 */
class FixMissingAccents extends Command
{
    protected $signature = 'app:fix-missing-accents {--dry-run : Solo cuenta cuántos registros cambiarían, sin guardar nada}';

    protected $description = 'Corrige tildes y eñes faltantes en contenido sembrado (site_settings, menús, páginas, noticias, FAQ, proyectos, convocatorias)';

    /**
     * Mapa de reemplazo. Claves = palabra tal como quedó mal (sin tilde/eñe).
     * El reemplazo respeta mayúsculas/minúsculas/versalitas generando las 3
     * variantes automáticamente a partir de la forma "Capitalizada" que se
     * escribe aquí.
     */
    protected array $palabras = [
        'Jose Joaquin' => 'José Joaquín',
        'Fundacion' => 'Fundación',
        'Corporacion' => 'Corporación',
        'Transito' => 'Tránsito',
        'Guia' => 'Guía',
        'Empatia' => 'Empatía',
        'Estandares' => 'Estándares',
        'Informacion' => 'Información',
        'Operacion' => 'Operación',
        'Gestion' => 'Gestión',
        'Institucion' => 'Institución',
        'Decision' => 'Decisión',
        'Seleccion' => 'Selección',
        'Publica' => 'Pública',
        'Publico' => 'Público',
        'Tecnologica' => 'Tecnológica',
        'Numeros' => 'Números',
        'Atencion' => 'Atención',
        'Rendicion' => 'Rendición',
        'Aerolineas' => 'Aerolíneas',
        'Estrategico' => 'Estratégico',
        'Modernizacion' => 'Modernización',
        'Vision' => 'Visión',
        'Practica' => 'Práctica',
        'Direccion' => 'Dirección',
        'Seccion' => 'Sección',
        'Contactanos' => 'Contáctanos',
        'Mision' => 'Misión',
        'Ingenieria' => 'Ingeniería',
        'Administracion' => 'Administración',
        'Titulo' => 'Título',
        'Ingles' => 'Inglés',
        'Postulacion' => 'Postulación',
        'Lo que mas' => 'Lo que más',
        'esta ubicado' => 'está ubicado',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $tocados = 0;

        // 1. site_settings.value
        foreach (SiteSetting::all() as $s) {
            $original = $s->value;
            $fijo = $this->fixText($original);
            if ($fijo !== $original) {
                $this->line("[site_settings:{$s->key}] {$original} -> {$fijo}");
                $tocados++;
                if (!$dryRun) {
                    $s->value = $fijo;
                    $s->save();
                }
            }
        }

        // 2. menu_items.label
        foreach (MenuItem::all() as $mi) {
            $original = $mi->label;
            $fijo = $this->fixText($original);
            if ($fijo !== $original) {
                $this->line("[menu_items:#{$mi->id}] {$original} -> {$fijo}");
                $tocados++;
                if (!$dryRun) {
                    $mi->label = $fijo;
                    $mi->save();
                }
            }
        }

        // 3. pages: title, meta_title, meta_description
        foreach (Page::all() as $p) {
            $cambios = [];
            foreach (['title', 'meta_title', 'meta_description'] as $campo) {
                $original = $p->{$campo};
                $fijo = $this->fixText($original);
                if ($fijo !== $original) {
                    $cambios[$campo] = $fijo;
                }
            }
            if ($cambios) {
                $this->line("[pages:{$p->key}] campos: " . implode(',', array_keys($cambios)));
                $tocados++;
                if (!$dryRun) {
                    $p->fill($cambios);
                    $p->save();
                }
            }
        }

        // 4. page_blocks.settings (JSON recursivo)
        $paginasConBloqueTocado = [];
        foreach (PageBlock::all() as $pb) {
            $original = $pb->settings;
            $fijo = $this->fixArrayRecursivo($original, null);
            if ($fijo !== $original) {
                $this->line("[page_blocks:#{$pb->id} tipo={$pb->type} page_id={$pb->page_id}]");
                $tocados++;
                if (!$dryRun) {
                    $pb->settings = $fijo;
                    $pb->save();
                    $paginasConBloqueTocado[$pb->page_id] = true;
                }
            }
        }

        // 5. news
        foreach (News::all() as $n) {
            $cambios = [];
            foreach (['title', 'excerpt', 'content', 'meta_title', 'meta_description'] as $campo) {
                $original = $n->{$campo};
                if (is_string($original)) {
                    $fijo = $this->fixText($original);
                    if ($fijo !== $original) {
                        $cambios[$campo] = $fijo;
                    }
                }
            }
            if ($cambios) {
                $this->line("[news:#{$n->id}] campos: " . implode(',', array_keys($cambios)));
                $tocados++;
                if (!$dryRun) {
                    $n->fill($cambios);
                    $n->save();
                }
            }
        }

        // 6. faqs
        foreach (Faq::all() as $f) {
            $cambios = [];
            foreach (['question', 'answer'] as $campo) {
                $original = $f->{$campo};
                $fijo = $this->fixText($original);
                if ($fijo !== $original) {
                    $cambios[$campo] = $fijo;
                }
            }
            if ($cambios) {
                $this->line("[faqs:#{$f->id}] campos: " . implode(',', array_keys($cambios)));
                $tocados++;
                if (!$dryRun) {
                    $f->fill($cambios);
                    $f->save();
                }
            }
        }

        // 7. projects
        foreach (Project::all() as $pr) {
            $cambios = [];
            foreach (['title', 'summary', 'description', 'meta_title', 'meta_description'] as $campo) {
                $original = $pr->{$campo};
                if (is_string($original)) {
                    $fijo = $this->fixText($original);
                    if ($fijo !== $original) {
                        $cambios[$campo] = $fijo;
                    }
                }
            }
            if ($cambios) {
                $this->line("[projects:#{$pr->id}] campos: " . implode(',', array_keys($cambios)));
                $tocados++;
                if (!$dryRun) {
                    $pr->fill($cambios);
                    $pr->save();
                }
            }
        }

        // 8. convocatorias
        foreach (Convocatoria::all() as $c) {
            $cambios = [];
            foreach (['title', 'short_description'] as $campo) {
                $original = $c->{$campo};
                if (is_string($original)) {
                    $fijo = $this->fixText($original);
                    if ($fijo !== $original) {
                        $cambios[$campo] = $fijo;
                    }
                }
            }
            $reqOriginal = $c->requirements;
            $reqFijo = $this->fixArrayRecursivo($reqOriginal, null);
            if ($reqFijo !== $reqOriginal) {
                $cambios['requirements'] = $reqFijo;
            }
            if ($cambios) {
                $this->line("[convocatorias:#{$c->id}] campos: " . implode(',', array_keys($cambios)));
                $tocados++;
                if (!$dryRun) {
                    $c->fill($cambios);
                    $c->save();
                }
            }
        }

        if (!$dryRun) {
            // Los page_blocks no disparan invalidación de caché de Page::byKey.
            foreach (array_keys($paginasConBloqueTocado) as $pageId) {
                $page = Page::find($pageId);
                if ($page) {
                    Page::clearCache($page->key);
                }
            }
            Cache::forget('site_settings');
            foreach (['header', 'footer', 'footer_secondary'] as $loc) {
                Cache::forget("menu_location_{$loc}");
            }
            Cache::forget('sitemap_xml');
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Registros corregidos: {$tocados}");

        return self::SUCCESS;
    }

    /**
     * Claves de array que son URLs/rutas/slugs, no texto visible. Nunca se
     * les debe agregar tildes: una tilde en una URL rompe el enlace.
     */
    protected array $clavesUrl = [
        'url', 'href', 'slug', 'link', 'cta_url', 'cta1_url', 'cta2_url',
        'bases_pdf', 'cover_image', 'gallery',
    ];

    protected function esClaveUrl(mixed $clave): bool
    {
        if (!is_string($clave)) {
            return false;
        }
        return in_array($clave, $this->clavesUrl, true) || str_ends_with($clave, '_url');
    }

    protected function fixArrayRecursivo(mixed $valor, mixed $clave): mixed
    {
        if (is_array($valor)) {
            $resultado = [];
            foreach ($valor as $k => $v) {
                $resultado[$k] = $this->fixArrayRecursivo($v, $k);
            }
            return $resultado;
        }
        if (is_string($valor)) {
            if ($this->esClaveUrl($clave)) {
                // No se le agregan tildes, pero sí se revierte si una
                // corrida anterior (con el bug de mb_strtoupper) ya le puso
                // tildes por error a esta URL.
                return $this->revertirUrl($valor);
            }
            return $this->fixText($valor);
        }
        return $valor;
    }

    /**
     * Deshace, solo en campos de tipo URL, cualquier tilde que una corrida
     * anterior de este comando le haya agregado por error. Las URLs/slugs
     * son casi siempre minúsculas, así que la búsqueda es insensible a
     * mayúsculas pero el reemplazo siempre se escribe en minúscula (nunca
     * se usa la forma "Capitalizada" del mapa, que rompería el casing del
     * slug, ej. dejar "/Guia-de-viaje" en vez de "/guia-de-viaje").
     */
    protected function revertirUrl(string $valor): string
    {
        foreach ($this->palabras as $rota => $correcta) {
            $buscar = $this->minusculaSegura($correcta);
            $reemplazo = strtolower($rota);
            $valor = str_ireplace($buscar, $reemplazo, $valor);
        }
        return $valor;
    }

    /**
     * Mayúscula segura para español: strtoupper() es seguro con UTF-8 (solo
     * toca a-z, deja intactos los bytes de caracteres multibyte) pero no
     * sabe convertir minúsculas acentuadas a mayúsculas acentuadas. mb_strtoupper
     * debería hacerlo, pero en este servidor no convierte bien í/ó (deja
     * "GUíA" en vez de "GUÍA"), así que la conversión de acentos se hace a
     * mano con strtr, sin depender de mbstring.
     */
    protected function mayusculaSegura(string $s): string
    {
        $s = strtoupper($s);
        return strtr($s, [
            'á' => 'Á', 'é' => 'É', 'í' => 'Í', 'ó' => 'Ó', 'ú' => 'Ú',
            'ñ' => 'Ñ', 'ü' => 'Ü',
        ]);
    }

    protected function minusculaSegura(string $s): string
    {
        $s = strtolower($s);
        return strtr($s, [
            'Á' => 'á', 'É' => 'é', 'Í' => 'í', 'Ó' => 'ó', 'Ú' => 'ú',
            'Ñ' => 'ñ', 'Ü' => 'ü',
        ]);
    }

    protected function fixText(?string $texto): ?string
    {
        if ($texto === null || $texto === '') {
            return $texto;
        }

        foreach ($this->palabras as $rota => $correcta) {
            // Genera las 3 variantes de capitalización a partir de la forma
            // "Capitalizada" escrita en el mapa. $rota nunca lleva tildes
            // (por definición: es la forma rota), así que strtolower/strtoupper
            // nativos de PHP son seguros para ella sin pasar por mbstring.
            $variantes = [
                $rota => $correcta,
                strtolower($rota) => $this->minusculaSegura($correcta),
                strtoupper($rota) => $this->mayusculaSegura($correcta),
            ];

            foreach ($variantes as $buscar => $reemplazo) {
                $patron = '/\b' . preg_quote($buscar, '/') . '\b/u';
                $texto = preg_replace($patron, $reemplazo, $texto);
            }
        }

        return $this->repararMayusculasMixtas($texto);
    }

    /**
     * Repara palabras en mayúsculas que se quedaron con un acento suelto en
     * minúscula (ej. "GUíA" en vez de "GUÍA"). Pasa esto en cualquier
     * ejecución, sea la primera vez (por si el texto ya traía el problema
     * de otra fuente) o una repetición sobre datos que una corrida anterior
     * de este mismo comando dejó a medias por un bug de mb_strtoupper en
     * este servidor.
     *
     * No depende de mbstring para decidir: solo mira si, al ignorar los
     * acentos en minúscula de la palabra, lo que queda es puro ASCII en
     * mayúsculas (ninguna a-z minúscula). Una palabra normal como "José" o
     * "Información" nunca cumple esto (les queda una minúscula ASCII), así
     * que no se tocan.
     */
    protected function repararMayusculasMixtas(string $texto): string
    {
        return preg_replace_callback('/\b[\p{L}]+\b/u', function ($m) {
            $palabra = $m[0];
            $soloAscii = preg_replace('/[áéíóúñü]/u', '', $palabra);
            if ($soloAscii !== '' && preg_match('/[A-Z]/', $soloAscii) && !preg_match('/[a-z]/', $soloAscii)) {
                return $this->mayusculaSegura($palabra);
            }
            return $palabra;
        }, $texto);
    }
}
