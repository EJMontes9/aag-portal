<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Convocatoria;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Convocatoria completa (DETALLE).
 *
 * Añade requisitos, cronograma y documentos: exactamente lo que la ficha
 * pública ya muestra en /convocatorias/{slug}.
 */
class ConvocatoriaResource extends JsonResource
{
    /** @var Convocatoria */
    public $resource;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'titulo' => (string) $this->resource->title,
            'slug' => (string) $this->resource->slug,
            'tipo' => (string) $this->resource->tipo,
            'area' => $this->resource->area,
            'modalidad' => $this->resource->modality,
            'descripcion_corta' => (string) $this->resource->short_description,
            'estado' => $this->resource->effective_status,
            'fecha_apertura' => $this->resource->opens_at?->toIso8601String(),
            'fecha_cierre' => $this->resource->closes_at?->toIso8601String(),
            'requisitos' => is_array($this->resource->requirements) ? array_values($this->resource->requirements) : [],
            'cronograma' => is_array($this->resource->cronograma) ? array_values($this->resource->cronograma) : [],
            'documentos' => $this->documentos(),
            'enlace_referencia' => $this->resource->enlace_referencia,
            'url' => route('convocatorias.show', $this->resource->slug),
        ];
    }

    /**
     * Documentos descargables, con URL absoluta ya resuelta.
     *
     * Se devuelve la URL y no la ruta interna de almacenamiento: la ruta no le
     * sirve de nada a un consumidor externo y describe cómo está organizado el
     * disco por dentro. El PDF de bases se funde en la misma lista porque para
     * quien consume es un documento más; que en la base de datos viva en su
     * propia columna es un detalle nuestro.
     *
     * Las entradas sin archivo se descartan en vez de devolverse con url null:
     * un documento que no se puede descargar no es información, es ruido.
     */
    private function documentos(): array
    {
        $documentos = [];

        if ($this->resource->bases_pdf) {
            $documentos[] = [
                'nombre' => 'Bases de la convocatoria',
                'tipo' => Convocatoria::fileTypeInfo($this->resource->bases_pdf)['label'],
                'url' => Storage::disk('public')->url($this->resource->bases_pdf),
            ];
        }

        foreach ((array) $this->resource->documentos as $doc) {
            // El formulario ha usado dos nombres de clave a lo largo del
            // tiempo; la vista pública contempla los dos y aquí se hace igual.
            $archivo = $doc['archivo'] ?? $doc['path'] ?? '';

            if (! $archivo) {
                continue;
            }

            $documentos[] = [
                'nombre' => $doc['nombre'] ?? basename($archivo),
                'tipo' => Convocatoria::fileTypeInfo($archivo)['label'],
                'url' => Storage::disk('public')->url($archivo),
            ];
        }

        return $documentos;
    }
}
