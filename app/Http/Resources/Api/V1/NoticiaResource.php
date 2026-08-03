<?php

namespace App\Http\Resources\Api\V1;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Noticia completa (DETALLE).
 *
 * Añade al resumen lo que solo tiene sentido en la ficha. Rigen las mismas
 * exclusiones que en NoticiaResumenResource: nada de autor, visitas, estado ni
 * campos de SEO.
 */
class NoticiaResource extends JsonResource
{
    /** @var News */
    public $resource;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'titulo' => (string) $this->resource->title,
            'slug' => (string) $this->resource->slug,
            'entradilla' => (string) $this->resource->excerpt,
            'categoria' => $this->resource->category
                ? [
                    'nombre' => (string) $this->resource->category->name,
                    'slug' => (string) $this->resource->category->slug,
                ]
                : null,
            // El cuerpo sale como HTML porque así se guarda. Ya viene saneado
            // desde el modelo (News::booted pasa HtmlSanitizer al guardar), así
            // que es el mismo HTML que la web pinta, sin sorpresas añadidas.
            'contenido' => (string) $this->resource->content,
            'imagen_portada' => $this->resource->cover_url,
            'imagen_portada_alt' => $this->resource->cover_image_alt,
            'tiempo_lectura_minutos' => $this->resource->reading_time,
            'fecha_publicacion' => $this->resource->published_at?->toIso8601String(),
            'url' => route('news.show', $this->resource->slug),
        ];
    }
}
