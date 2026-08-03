<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Convocatoria;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Convocatoria tal y como sale en el LISTADO.
 *
 * Igual que en las noticias, los campos van enumerados uno a uno para que una
 * columna nueva en la tabla no acabe publicada sin que nadie lo haya decidido.
 *
 * El estado que se devuelve es `effective_status`, no la columna `status`: una
 * convocatoria marcada como vigente cuya fecha de cierre ya pasó está cerrada
 * en la práctica, y la web la presenta así. Devolver el valor crudo haría que
 * un consumidor anunciase como abierto un proceso que no admite nada.
 */
class ConvocatoriaResumenResource extends JsonResource
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
            'url' => route('convocatorias.show', $this->resource->slug),
        ];
    }
}
