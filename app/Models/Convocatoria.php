<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Convocatoria extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title', 'slug', 'area', 'modality', 'short_description', 'requirements',
        'bases_pdf', 'opens_at', 'closes_at', 'status', 'alert_mode', 'alert_frequency',
        'featured_on_home',
        // Nuevos campos ──────────────────────────────────────────────────────
        'tipo',             // proceso | aviso
        'layout_type',      // poster | banner | minimal  (solo para avisos)
        'imagen',           // imagen para avisos
        'video_url',        // URL YouTube/Vimeo para avisos
        'show_logo',        // mostrar logo institucional en avisos
        'cronograma',       // [{etapa, fecha, hora}] para ambos tipos
        'enlace_referencia',// URL de referencia para procesos
        'documentos',       // [{nombre, archivo}] para procesos
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'tipo', 'status', 'closes_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => 'Convocatoria creada',
                'updated' => 'Convocatoria actualizada',
                'deleted' => 'Convocatoria eliminada',
                default   => $e,
            });
    }

    protected $casts = [
        'requirements'  => 'array',
        'cronograma'    => 'array',
        'documentos'    => 'array',
        'opens_at'      => 'datetime',
        'closes_at'     => 'datetime',
        'featured_on_home' => 'boolean',
        'show_logo'     => 'boolean',
    ];

    protected $appends = ['effective_status', 'embed_url'];

    protected static function booted(): void
    {
        static::saving(function (self $m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->title);
            }
        });

        $bust = function (self $m) {
            Cache::forget('home_convocatoria');
            Cache::forget("convocatoria_{$m->id}");
        };
        static::saved($bust);
        static::deleted($bust);
    }

    /**
     * Estado efectivo: si el campo BD dice 'vigente' pero ya pasamos closes_at,
     * reporta 'cerrada'. Las consultas que ya filtran por status='vigente'
     * deben usar el scope vigentes() en su lugar.
     */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'vigente' && $this->closes_at && $this->closes_at->isPast()) {
            return 'cerrada';
        }
        return (string) $this->status;
    }

    /**
     * Convierte YouTube / Vimeo URL a URL de embed.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        // YouTube: watch?v=ID  o  youtu.be/ID
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $this->video_url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
        }

        // Vimeo: vimeo.com/ID
        if (preg_match('/vimeo\.com\/(\d+)/', $this->video_url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1] . '?title=0&byline=0';
        }

        return null;
    }

    /** Filtra solo convocatorias realmente vigentes (campo + fecha futura o sin cierre). */
    public function scopeVigentes(Builder $q): Builder
    {
        return $q->where('status', 'vigente')
            ->where(function ($q2) {
                $q2->whereNull('closes_at')->orWhere('closes_at', '>', now());
            });
    }

    /** Scope para avisos simples vigentes. */
    public function scopeAvisosVigentes(Builder $q): Builder
    {
        return $q->where('tipo', 'aviso')->where('status', 'vigente');
    }

    public static function featured(): ?self
    {
        return Cache::rememberForever('home_convocatoria', function () {
            return static::vigentes()
                ->where('featured_on_home', true)
                ->orderBy('closes_at')
                ->first();
        });
    }

    /**
     * Igual que find(), pero cacheado -- para el bloque de home que fija una
     * convocatoria específica en vez de usar featured(). Se invalida con el
     * mismo hook saved/deleted de arriba.
     */
    public static function cached(int $id): ?self
    {
        return Cache::rememberForever("convocatoria_{$id}", fn () => static::find($id));
    }

    public function isAlertActive(): bool
    {
        return $this->alert_mode !== 'none' && $this->effective_status === 'vigente';
    }

    /**
     * Devuelve la etiqueta legible del tipo de archivo según su extensión.
     *
     * Antes devolvía además clases de Tailwind ('bg' y 'text') con un color
     * pastel por extensión. Se eliminaron por dos motivos: no pertenecen a la
     * paleta institucional, y además NUNCA llegaban a aplicarse — Tailwind
     * escanea las rutas de `content` en tailwind.config.js, donde app/Models
     * no está incluido, así que esas clases jamás se compilaban y el badge
     * salía sin estilo. Las vistas ahora usan clases literales propias.
     */
    public static function fileTypeInfo(string $path): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match($ext) {
            'pdf'               => ['label' => 'PDF'],
            'doc', 'docx'       => ['label' => 'Word'],
            'xls', 'xlsx'       => ['label' => 'Excel'],
            'ppt', 'pptx'       => ['label' => 'PPT'],
            'zip', 'rar', '7z'  => ['label' => 'ZIP'],
            'jpg', 'jpeg', 'png', 'gif', 'webp' => ['label' => 'IMG'],
            default             => ['label' => strtoupper($ext) ?: 'DOC'],
        };
    }
}
