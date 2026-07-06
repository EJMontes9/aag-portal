<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FormSubmission extends Model
{
    use LogsActivity;

    protected static bool $logUnguarded = false;
    protected $fillable = [
        'form_id',
        'data',
        'ip_address',
        'user_agent',
        'read_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['form_id', 'ip_address', 'read_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => 'Nueva respuesta recibida',
                'updated' => 'Respuesta marcada como leída',
                'deleted' => 'Respuesta eliminada',
                default   => $e,
            });
    }

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}
