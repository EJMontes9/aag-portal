<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Redirección de una dirección antigua a su equivalente en el portal nuevo.
 *
 * Ver la migración create_redirects_table para el porqué.
 */
class Redirect extends Model
{
    protected $fillable = [
        'from_path', 'to_path', 'status_code', 'is_active', 'notes',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'status_code'  => 'integer',
        'hits'         => 'integer',
        'last_used_at' => 'datetime',
    ];

    protected const CACHE_KEY = 'redirects_activas';

    protected static function booted(): void
    {
        static::saved(fn () => static::olvidar());
        static::deleted(fn () => static::olvidar());
    }

    public static function olvidar(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Todas las redirecciones activas, indexadas por ruta de origen.
     *
     * Se cargan de una vez y se dejan en caché porque la comprobación ocurre en
     * CADA 404: si cada una fuese una consulta, bastaría con que alguien pidiera
     * direcciones inexistentes en bucle para castigar la base de datos. Son unos
     * pocos cientos de filas como mucho, así que caben de sobra en memoria.
     */
    public static function activas(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()
                ->where('is_active', true)
                ->pluck('to_path', 'from_path')
                ->toArray();
        });
    }

    /**
     * Normaliza una ruta para poder compararlas sin sorpresas.
     *
     * "/Quienes-Somos/" y "quienes-somos" son la misma dirección a efectos
     * prácticos, pero como texto no coinciden. Se unifica: siempre con barra
     * inicial, sin barra final, en minúsculas y sin la cadena de consulta.
     *
     * La barra final se quita SALVO en la raíz, que es solo "/" y se quedaría
     * en cadena vacía.
     */
    public static function normalizar(string $ruta): string
    {
        $ruta = strtok($ruta, '?');
        $ruta = '/' . trim(mb_strtolower($ruta), '/');

        return $ruta;
    }

    /**
     * Suma una visita. Se llama desde el middleware.
     *
     * Usa una consulta directa en vez de save() a propósito: no debe disparar
     * los eventos del modelo, porque saved() limpia la caché de redirecciones y
     * la estaría tirando en cada visita, justo lo que la caché venía a evitar.
     */
    public function registrarUso(): void
    {
        static::query()
            ->where('id', $this->id)
            ->update([
                'hits'         => $this->hits + 1,
                'last_used_at' => now(),
            ]);
    }
}
