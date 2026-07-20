<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class News extends Model
{
    use LogsActivity;
    protected $table = 'news';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'is_published', 'category_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => 'Noticia creada',
                'updated' => 'Noticia actualizada',
                'deleted' => 'Noticia eliminada',
                default   => $e,
            });
    }

    protected $fillable = [
        'title', 'slug', 'category_id', 'author_id', 'excerpt', 'content', 'content_blocks',
        'cover_image', 'cover_image_alt', 'status', 'published_at',
        'featured_on_home', 'meta_title', 'meta_description', 'views_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'featured_on_home' => 'boolean',
        'content_blocks' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->title);
            }
            // Si pasa a published y no tiene fecha, ponemos ahora.
            if ($m->status === 'published' && empty($m->published_at)) {
                $m->published_at = now();
            }

            // SEGURIDAD -- El cuerpo se pinta sin escapar ({!! !!}), asi que se
            // limpia ANTES de guardar. La barra del editor limita lo que se
            // puede hacer con el raton, no lo que llega al servidor: sin esto,
            // quien tenga acceso al panel puede inyectar JavaScript que se
            // ejecuta en el navegador de cada visitante.
            if ($m->isDirty('content')) {
                $m->content = \App\Services\HtmlSanitizer::limpiar($m->content);
            }

            // Los bloques de contenido de la noticia guardan su HTML dentro de
            // un JSON. El bloque "texto" tambien se pinta sin escapar, asi que
            // se limpia igual, recorriendo la estructura.
            if ($m->isDirty('content_blocks') && is_array($m->content_blocks)) {
                $bloques = $m->content_blocks;

                foreach ($bloques as $i => $bloque) {
                    if (($bloque['type'] ?? null) === 'text' && isset($bloque['data']['content'])) {
                        $bloques[$i]['data']['content'] =
                            \App\Services\HtmlSanitizer::limpiar($bloque['data']['content']);
                    }
                }

                $m->content_blocks = $bloques;
            }
        });

        $bust = function () {
            Cache::forget('news_home_highlights');
            Cache::forget('sitemap_xml');
        };
        static::saved($bust);
        static::deleted($bust);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Solo noticias publicadas y con fecha en el pasado o presente. */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->published()->where('featured_on_home', true);
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (! $this->cover_image) return null;
        return Storage::disk('public')->url($this->cover_image);
    }

    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->content));
        return max(1, (int) ceil($words / 220));
    }

    public function incrementViews(): void
    {
        // Usa update() para evitar disparar saving() y el bust de cache.
        static::where('id', $this->id)->increment('views_count');
    }

    /**
     * Devuelve true si la noticia tiene bloques estructurados de multimedia.
     * Se usa para decidir si renderizar el Builder o el legacy `content` (RichEditor).
     */
    public function hasContentBlocks(): bool
    {
        return is_array($this->content_blocks) && count($this->content_blocks) > 0;
    }
}
