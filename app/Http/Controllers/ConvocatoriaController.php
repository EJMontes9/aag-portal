<?php

namespace App\Http\Controllers;

use App\Models\Convocatoria;
use Illuminate\Http\Request;

class ConvocatoriaController extends Controller
{
    /** Listado público de convocatorias. */
    public function index(Request $request)
    {
        $tipo = $request->get('tipo'); // proceso | aviso | null

        $vigentes = Convocatoria::vigentes()
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->orderBy('closes_at')
            ->get();

        // Archivo histórico. Se declara como closure y no como query suelta
        // porque hace falta recorrerlo dos veces (una para los años del
        // desplegable y otra para la página en curso) y un Builder reutilizado
        // arrastraría los where del primer uso al segundo.
        $archivo = fn () => Convocatoria::where('status', 'cerrada')
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo));

        // Los años salen de los datos, no de un rango fijo: así el desplegable
        // nunca ofrece un año que devuelva cero resultados.
        $anios = $archivo()
            ->whereNotNull('closes_at')
            ->selectRaw('YEAR(closes_at) as anio')
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio')
            ->map(fn ($a) => (int) $a);

        // Solo se acepta un año presente en la lista. Un ?anio=abc o un año
        // inventado dejarían el listado vacío sin explicar por qué, así que se
        // ignoran y se muestra el archivo completo.
        $anio = $anios->contains((int) $request->get('anio')) ? (int) $request->get('anio') : null;

        $cerradas = $archivo()
            ->when($anio, fn ($q) => $q->whereYear('closes_at', $anio))
            ->orderByDesc('closes_at')
            ->paginate(15)
            // withQueryString conserva tipo y año al cambiar de página; el
            // fragment devuelve al bloque del archivo en vez de al principio
            // de la página, que en desktop queda muy por encima.
            ->withQueryString()
            ->fragment('archivo');

        $avisos = Convocatoria::avisosVigentes()
            ->when(!$tipo || $tipo === 'aviso', fn ($q) => $q)
            ->get();

        return view('pages.convocatoria.index', compact('vigentes', 'cerradas', 'avisos', 'tipo', 'anios', 'anio'));
    }

    /** Detalle de una convocatoria — la página interna con documentos. */
    public function show(string $slug)
    {
        $conv = Convocatoria::where('slug', $slug)
            ->whereIn('status', ['vigente', 'cerrada'])
            ->firstOrFail();

        $cronograma   = is_array($conv->cronograma)   ? $conv->cronograma   : [];
        $documentos   = is_array($conv->documentos)   ? $conv->documentos   : [];
        $requirements = is_array($conv->requirements) ? $conv->requirements : [];

        $docTotal = count($documentos) + ($conv->bases_pdf ? 1 : 0);
        $isOpen   = $conv->effective_status === 'vigente';
        $closes   = $conv->closes_at;

        // SEO dinámico
        $metaTitle = $conv->title . ' | Convocatoria';
        $metaDesc  = $conv->short_description ?: 'Consulta los documentos y detalles de esta convocatoria de la Autoridad Aeroportuaria de Guayaquil.';

        return view('pages.convocatoria.show', compact(
            'conv', 'cronograma', 'documentos', 'requirements',
            'docTotal', 'isOpen', 'closes', 'metaTitle', 'metaDesc'
        ));
    }
}
