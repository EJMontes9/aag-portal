<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Redireccion de una direccion antigua a su equivalente en el portal nuevo.
 *
 * Ver la migracion create_redirects_table para el porque.
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
     * Se cargan de una vez y se dejan en cache porque la comprobacion ocurre en
     * CADA 404: si cada una fuese una consulta, bastaria con que alguien pidiera
     * direcciones inexistentes en bucle para castigar la base de datos. Son unos
     * pocos cientos de filas como mucho, asi que caben de sobra en memoria.
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
     * "/Quienes-Somos/" y "quienes-somos" son la misma direccion a efectos
     * practicos, pero como texto no coinciden. Se unifica: siempre con barra
     * inicial, sin barra final, en minusculas y sin la cadena de consulta.
     *
     * La barra final se quita SALVO en la raiz, que es solo "/" y se quedaria
     * en cadena vacia.
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
     * Usa una consulta directa en vez de save() a proposito: no debe disparar
     * los eventos del modelo, porque saved() limpia la cache de redirecciones y
     * la estaria tirando en cada visita, justo lo que la cache venia a evitar.
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
