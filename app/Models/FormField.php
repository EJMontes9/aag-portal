<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FormField extends Model
{
    protected $fillable = [
        'form_id',
        'label',
        'field_key',
        'type',
        'placeholder',
        'help_text',
        'required',
        'min_length',
        'max_length',
        'options',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'options'   => 'array',
        'required'  => 'boolean',
        'is_active' => 'boolean',
    ];

    /** Tipos de campo disponibles con su etiqueta y si soportan opciones */
    public const TYPES = [
        'text'     => ['label' => 'Texto corto',       'has_options' => false, 'icon' => '✏️'],
        'email'    => ['label' => 'Correo electrónico', 'has_options' => false, 'icon' => '✉️'],
        'tel'      => ['label' => 'Teléfono',           'has_options' => false, 'icon' => '📞'],
        'number'   => ['label' => 'Número',             'has_options' => false, 'icon' => '🔢'],
        'date'     => ['label' => 'Fecha',              'has_options' => false, 'icon' => '📅'],
        'textarea' => ['label' => 'Texto largo',        'has_options' => false, 'icon' => '📄'],
        'select'   => ['label' => 'Lista desplegable',  'has_options' => true,  'icon' => '▼'],
        'radio'    => ['label' => 'Opción única',       'has_options' => true,  'icon' => '⚪'],
        'checkbox' => ['label' => 'Casilla de verificación', 'has_options' => false, 'icon' => '☑️'],
    ];

    public static function typeOptions(): array
    {
        return collect(self::TYPES)->mapWithKeys(
            fn ($meta, $key) => [$key => $meta['label']]
        )->all();
    }

    public function hasOptions(): bool
    {
        return self::TYPES[$this->type]['has_options'] ?? false;
    }

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    // ─── Booted ──────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $field) {
            if (empty($field->field_key)) {
                $field->field_key = Str::snake(Str::ascii($field->label));
            }
            if (! isset($field->sort_order)) {
                $field->sort_order = (int) static::where('form_id', $field->form_id)->max('sort_order') + 1;
            }
        });
    }
}
