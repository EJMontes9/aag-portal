<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Page extends Model
{
    use LogsActivity;
    protected $fillable = ['key', 'title', 'slug', 'status', 'meta_title', 'meta_description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'is_active', 'is_published'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => 'Página creada',
                'updated' => 'Página actualizada',
                'deleted' => 'Página eliminada',
                default   => $e,
            });
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('sort_order');
    }

    public function activeBlocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->where('is_active', true)->orderBy('sort_order');
    }

    public static function byKey(string $key): ?self
    {
        return Cache::rememberForever("page_{$key}", function () use ($key) {
            return static::with('activeBlocks')->where('key', $key)->where('status', 'published')->first();
        });
    }

    public static function clearCache(string $key = null): void
    {
        if ($key) {
            Cache::forget("page_{$key}");
        } else {
            Cache::flush();
        }
    }

    protected static function booted(): void
    {
        static::saved(function (self $p) {
            static::clearCache($p->key);
            Cache::forget('sitemap_xml');
        });
        static::deleted(function (self $p) {
            static::clearCache($p->key);
            Cache::forget('sitemap_xml');
        });
    }
}
