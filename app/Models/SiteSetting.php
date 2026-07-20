<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'group', 'type', 'value', 'label', 'description'];

    /**
     * Ajustes ya resueltos para esta peticion.
     *
     * ── Por que hace falta ──────────────────────────────────────────────────
     * Cache::rememberForever() evita ir a la tabla site_settings, pero NO evita
     * ir a la cache: con CACHE_STORE=database (que es lo que hay en cPanel, sin
     * Redis) cada lectura es un "select * from cache where key in (?)".
     *
     * Y settings() se llama muchisimo al pintar una pagina: cada bloque, cada
     * componente, cada color de la linea grafica. Medido antes de este cambio:
     * la portada hacia 86 consultas identicas a la cache, y /transparencia
     * llegaba a 4631 (mas de 1,4 segundos solo en base de datos) porque recorre
     * cientos de meses y documentos.
     *
     * Todas devuelven exactamente lo mismo dentro de una misma peticion, asi
     * que basta con quedarse el resultado en memoria: la primera llamada va a
     * la cache, el resto salen de aqui.
     *
     * Es memoria por peticion, no un cache persistente: PHP la descarta al
     * terminar. No hay riesgo de servir datos viejos a otro visitante.
     */
    protected static ?array $memoria = null;

    protected static function booted(): void
    {
        static::saved(fn () => static::olvidar());
        static::deleted(fn () => static::olvidar());
    }

    /**
     * Invalida la cache persistente Y la de esta peticion.
     *
     * Las dos, y en este orden: si solo se limpiara la de disco, el panel
     * seguiria mostrando el valor viejo al recargar el formulario tras guardar,
     * porque esa misma peticion ya tiene el array en memoria.
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
