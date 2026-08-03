<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'group', 'type', 'value', 'label', 'description'];

    /**
     * Ajustes ya resueltos para esta petición.
     *
     * ── Por qué hace falta ──────────────────────────────────────────────────
     * Cache::rememberForever() evita ir a la tabla site_settings, pero NO evita
     * ir a la caché: con CACHE_STORE=database (que es lo que hay en cPanel, sin
     * Redis) cada lectura es un "select * from cache where key in (?)".
     *
     * Y settings() se llama muchísimo al pintar una página: cada bloque, cada
     * componente, cada color de la línea gráfica. Medido antes de este cambio:
     * la portada hacía 86 consultas idénticas a la caché, y /transparencia
     * llegaba a 4631 (más de 1,4 segundos solo en base de datos) porque recorre
     * cientos de meses y documentos.
     *
     * Todas devuelven exactamente lo mismo dentro de una misma petición, así
     * que basta con quedarse el resultado en memoria: la primera llamada va a
     * la caché, el resto salen de aquí.
     *
     * Es memoria por petición, no un caché persistente: PHP la descarta al
     * terminar. No hay riesgo de servir datos viejos a otro visitante.
     */
    protected static ?array $memoria = null;

    protected static function booted(): void
    {
        static::saved(fn () => static::olvidar());
        static::deleted(fn () => static::olvidar());
    }

    /**
     * Invalida la caché persistente Y la de esta petición.
     *
     * Las dos, y en este orden: si solo se limpiara la de disco, el panel
     * seguiría mostrando el valor viejo al recargar el formulario tras guardar,
     * porque esa misma petición ya tiene el array en memoria.
     */
    public static function olvidar(): void
    {
        Cache::forget('site_settings');
        static::$memoria = null;
    }

    public static function all($columns = ['*'])
    {
        return parent::all($columns);
    }

    public static function allCached(): array
    {
        if (static::$memoria !== null) {
            return static::$memoria;
        }

        return static::$memoria = Cache::rememberForever('site_settings', function () {
            return static::query()->get()->mapWithKeys(fn ($s) => [$s->key => $s->castedValue()])->toArray();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::allCached()[$key] ?? $default;
    }

    public function castedValue(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json', 'array' => json_decode($this->value ?? '[]', true),
            default => $this->value,
        };
    }
}
