<?php

namespace App\Http\Controllers;

use App\Models\LotaipMonth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Entrega el listado de documentos de UN mes de Transparencia, bajo demanda.
 *
 * POR QUÉ EXISTE
 * ---------------
 * El bloque "Navegador de Transparencia" (blocks/transparency-browser.blade.php)
 * solía incrustar en la página el HTML completo de TODOS los documentos de
 * TODOS los meses y años, aunque el visitante solo puede ver un mes a la vez
 * (el resto queda oculto con x-show, pero igual viaja al navegador). Con el
 * archivo histórico de LOTAIP (más de mil documentos) eso convertía la página
 * en varios megabytes y quince segundos de carga.
 *
 * Ahora la página solo envía los títulos de mes y su conteo; el detalle de un
 * mes concreto se pide aquí, solo cuando el visitante lo abre, y solo una vez
 * (el navegador lo guarda del lado del cliente mientras no recargue la
 * página).
 */
class TransparencyController extends Controller
{
    public function documentos(Request $request, LotaipMonth $month)
    {
        // Un mes inactivo, o de un año inactivo, no debe poder consultarse
        // adivinando su id: sería información que el administrador retiró.
        abort_unless($month->is_active && $month->year?->is_active, 404);

        // Los meses en modo "redirección" no tienen documentos propios que
        // listar aquí: su enlace ya se muestra directamente en la página.
        abort_if($month->mode === 'redirect', 404);

        // Cacheado por mes: el archivo histórico no cambia salvo que alguien
        // suba o edite documentos desde el panel, y ambos modelos ya limpian
        // esta clave al guardarse (ver LotaipDocument::booted).
        $html = Cache::remember(
            "transparency_month_html_{$month->id}",
            300,
            fn () => $this->render($month)
        );

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    protected function render(LotaipMonth $month): string
    {
        $exts = $month->getEffectiveExtensions();

        $documentos = $month->activeDocuments()
            ->when(! empty($exts), fn ($q) => $q->whereIn('extension', $exts))
            ->get()
            // El enlace de un documento externo depende de que el subdominio
            // esté configurado; si no lo está, url() devuelve vacío y no hay
            // nada útil que mostrar para ese registro.
            ->filter(fn ($d) => $d->url !== '')
            ->values();

        $grupos = $documentos
            ->groupBy(fn ($d) => $d->literal ?: '')
            ->map(fn ($grupo, $literal) => [
                'literal' => $literal,
                'documents' => $grupo->values()->map(fn ($d) => [
                    'title' => $d->title,
                    'url' => $d->url,
                    'is_external' => $d->isExternal(),
                    'extension' => $d->extension,
                    'size_human' => $d->size_human,
                ])->all(),
            ])
            ->values();

        return view('blocks.transparency-documents-partial', [
            'grupos' => $grupos,
        ])->render();
    }
}
