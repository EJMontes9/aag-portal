<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use App\Models\Form;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class FormBlock extends BlockType
{
    public static function key(): string   { return 'form'; }
    public static function label(): string { return 'Formulario'; }
    public static function icon(): string  { return 'heroicon-o-document-text'; }
    public static function view(): string  { return 'blocks.form'; }

    public static function defaults(): array
    {
        return [
            'form_id'             => null,
            'section_title'       => '',
            'section_description' => '',
            'layout'              => 'centered',
            'bg_color'            => '',
        ];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\Section::make('Selección del formulario')
                    ->schema([
                        Forms\Components\Select::make('form_id')
                            ->label('Formulario a mostrar')
                            ->options(fn () => Form::active()->pluck('name', 'id')->all())
                            ->required()
                            ->searchable()
                            ->helperText('Crea el formulario primero en Contenido → Formularios.'),

                        Forms\Components\TextInput::make('section_title')
                            ->label('Título de la sección')
                            ->placeholder('Contáctanos')
                            ->maxLength(120),

                        Forms\Components\Textarea::make('section_description')
                            ->label('Descripción de la sección')
                            ->placeholder('Escríbenos y te responderemos en menos de 24 horas.')
                            ->rows(2)
                            ->maxLength(400),
                    ])->columns(1),

                Forms\Components\Section::make('Presentación')
                    ->schema([
                        Forms\Components\Select::make('layout')
                            ->label('Diseño')
                            ->options([
                                'centered' => 'Centrado (ancho medio)',
                                'full'     => 'Ancho completo',
                                'split'    => 'Dos columnas (info + form)',
                            ])
                            ->default('centered'),

                        Forms\Components\ColorPicker::make('bg_color')
                            ->label('Color de fondo')
                            ->default(''),
                    ])->columns(2)->collapsed(),
            ]);
    }
}
