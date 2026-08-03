<?php

namespace App\Http\Resources\Api\V1;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Noticia tal y como sale en el LISTADO.
 *
 * Los campos se enumeran uno a uno a propósito, y nunca se devuelve el modelo
 * entero. Un `return parent::toArray($request)` publicaría cualquier columna
 * que se añada a la tabla en el futuro sin que nadie lo decida: bastaría con
 * que alguien guardase una nota interna o un dato de contacto en un campo
 * nuevo para que la API lo empezara a servir sola. Esta lista es el contrato.
 *
 * Quedan FUERA de forma deliberada:
 *   - author_id / author  Identifica a una persona concreta de la institución.
 *                         La web publica la firma, no el registro de usuario.
 *   - views_count         Métrica interna, no información pública.
 *   - status              Solo salen publicadas: el campo no aporta nada y
 *                         revelaría que existe contenido en otros estados.
 *   - meta_title/desc     Son para el <head> de la web, no para un consumidor.
 */
class NoticiaResumenResource extends JsonResource
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
            'imagen_portada' => $this->resource->cover_url,
            'fecha_publicacion' => $this->resource->published_at?->toIso8601String(),
            'url' => route('news.show', $this->resource->slug),
        ];
    }
}
