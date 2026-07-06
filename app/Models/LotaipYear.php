<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class LotaipYear extends Model
{
    protected $fillable = ['section', 'year', 'allowed_extensions', 'is_active', 'sort_order'];

    protected $casts = [
        'allowed_extensions' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $bust = fn () => Cache::forget('transparency_tree');
        static::saved($bust);
        static::deleted($bust);
    }

    public function months(): HasMany
    {
        return $this->hasMany(LotaipMonth::class, 'year_id')->orderBy('month');
    }

    public function activeMonths(): HasMany
    {
        return $this->hasMany(LotaipMonth::class, 'year_id')->where('is_active', true)->orderBy('month');
    }

    public function scopeForSection(Builder $q, string $section): Builder
    {
        return $q->where('section', $section)->where('is_active', true);
    }
}
