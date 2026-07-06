<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class LotaipDocument extends Model
{
    protected $fillable = [
        'month_id', 'title', 'literal', 'file_path', 'extension',
        'file_size', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    protected static function booted(): void
    {
        // Cuando se sube un archivo, autodetectar extension y tamaño
        static::saving(function (self $m) {
            if ($m->file_path) {
                $m->extension = strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION));
                $absPath = Storage::disk('public')->path($m->file_path);
                if (file_exists($absPath) && empty($m->file_size)) {
                    $m->file_size = filesize($absPath);
                }
            }
        });

        $bust = fn () => Cache::forget('transparency_tree');
        static::saved($bust);
        static::deleted($bust);
    }

    public function month(): BelongsTo
    {
        return $this->belongsTo(LotaipMonth::class, 'month_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getSizeHumanAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) return '';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, $i === 0 ? 0 : 1) . ' ' . $units[$i];
    }

    public function getIconAttribute(): string
    {
        return match ($this->extension) {
            'pdf' => 'fa-file-pdf',
            'csv', 'xlsx', 'xls' => 'fa-file-csv',
            'doc', 'docx' => 'fa-file-word',
            'jpg', 'jpeg', 'png' => 'fa-file-image',
            default => 'fa-file',
        };
    }
}
