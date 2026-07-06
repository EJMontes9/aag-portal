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

        $cerradas = Convocatoria::where('status', 'cerrada')
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->orderByDesc('closes_at')
            ->limit(20)
            ->get();

        $avisos = Convocatoria::avisosVigentes()
            ->when(!$tipo || $tipo === 'aviso', fn ($q) => $q)
            ->get();

        return view('pages.convocatoria.index', compact('vigentes', 'cerradas', 'avisos', 'tipo'));
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
