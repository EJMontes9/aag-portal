<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Media extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name', 'file_name', 'disk', 'path',
        'mime_type', 'extension', 'size',
        'width', 'height', 'alt_text', 'type', 'folder',
    ];

    protected $appends = ['url', 'size_formatted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'path', 'type', 'alt_text', 'folder'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => 'Archivo subido a la galería',
                'updated' => 'Archivo actualizado',
                'deleted' => 'Archivo eliminado',
                default   => $e,
            });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 1) . ' MB';
        if ($bytes >= 1_024)    return round($bytes / 1_024, 0)     . ' KB';
        return $bytes . ' B';
    }

    public function getDimensionsAttribute(): ?string
    {
        if ($this->width && $this->height) {
            return "{$this->width}×{$this->height}";
        }
        return null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────────

    public function scopeImages($q)  { return $q->where('type', 'image'); }
    public function scopeVideos($q)  { return $q->where('type', 'video'); }
    public function scopeDocs($q)    { return $q->where('type', 'document'); }
}
