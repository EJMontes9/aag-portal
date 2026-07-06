<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Form extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'success_message',
        'submit_label',
        'notify_emails',
        'store_submissions',
        'is_active',
    ];

    protected $casts = [
        'notify_emails'     => 'array',
        'store_submissions' => 'boolean',
        'is_active'         => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'is_active', 'notify_emails'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => 'Formulario creado',
                'updated' => 'Formulario actualizado',
                'deleted' => 'Formulario eliminado',
                default   => $e,
            });
    }

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function activeFields(): HasMany
    {
        return $this->hasMany(FormField::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest();
    }

    public function unreadSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->whereNull('read_at');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Booted ──────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->name);
            }
        });
    }
}
