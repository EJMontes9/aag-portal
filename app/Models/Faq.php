<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Faq extends Model
{
    protected $fillable = [
        'question', 'answer', 'category_id', 'sort_order', 'is_active', 'featured',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        $bust = function () {
            Cache::forget('faqs_public');
            Cache::forget('sitemap_xml');
        };
        static::saved($bust);
        static::deleted($bust);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'category_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->active()->where('featured', true);
    }
}
