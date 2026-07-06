<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'title', 'slug', 'summary', 'description', 'gallery', 'cover_image',
        'status', 'budget', 'start_date', 'end_date', 'location', 'milestones',
        'meta_title', 'meta_description', 'is_published', 'sort_order',
    ];

    protected $casts = [
        'gallery' => 'array',
        'milestones' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->title);
            }
        });

        $bust = function () {
            Cache::forget('projects_public');
            Cache::forget('sitemap_xml');
        };
        static::saved($bust);
        static::deleted($bust);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image
            ? Storage::disk('public')->url($this->cover_image)
            : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'planificado' => 'Planificado',
            'en_curso' => 'En curso',
            'completado' => 'Completado',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'en_curso' => 'success',
            'completado' => 'info',
            'planificado' => 'warning',
            default => 'gray',
        };
    }
}
