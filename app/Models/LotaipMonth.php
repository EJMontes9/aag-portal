<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class LotaipMonth extends Model
{
    protected $fillable = [
        'year_id', 'month', 'mode', 'redirect_url', 'redirect_label',
        'allowed_extensions', 'is_active',
    ];

    protected $casts = [
        'allowed_extensions' => 'array',
        'is_active' => 'boolean',
    ];

    public const MONTH_NAMES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    protected static function booted(): void
    {
        $bust = fn () => Cache::forget('transparency_tree');
        static::saved($bust);
        static::deleted($bust);
    }

    public function year(): BelongsTo
    {
        return $this->belongsTo(LotaipYear::class, 'year_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LotaipDocument::class, 'month_id')->orderBy('sort_order');
    }

    public function activeDocuments(): HasMany
    {
        return $this->hasMany(LotaipDocument::class, 'month_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function getNameAttribute(): string
    {
        return self::MONTH_NAMES[$this->month] ?? "Mes {$this->month}";
    }

    /**
     * Devuelve las extensiones permitidas resolviendo herencia mes -> año.
     * Si ambos son null, devuelve null (= mostrar todo).
     */
    public function getEffectiveExtensions(): ?array
    {
        if (! empty($this->allowed_extensions)) {
            return $this->allowed_extensions;
        }
        return $this->year?->allowed_extensions;
    }

    /**
     * Documentos filtrados segun extensiones efectivas.
     */
    public function getVisibleDocuments()
    {
        $exts = $this->getEffectiveExtensions();
        $q = $this->activeDocuments();
        if (! empty($exts)) {
            $q->whereIn('extension', $exts);
        }
        return $q->get();
    }
}
