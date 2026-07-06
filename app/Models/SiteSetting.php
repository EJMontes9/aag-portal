<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'group', 'type', 'value', 'label', 'description'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings'));
        static::deleted(fn () => Cache::forget('site_settings'));
    }

    public static function all($columns = ['*'])
    {
        return parent::all($columns);
    }

    public static function allCached(): array
    {
        return Cache::rememberForever('site_settings', function () {
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
