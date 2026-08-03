<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use App\Models\FaqCategory;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class FaqAccordionBlock extends BlockType
{
    public static function key(): string { return 'faq_accordion'; }
    public static function label(): string { return 'Preguntas frecuentes (acordeón)'; }
    public static function icon(): string { return 'heroicon-o-question-mark-circle'; }
    public static function view(): string { return 'blocks.faq-accordion'; }

    public static function defaults(): array
    {
        return [
            'kicker' => 'CENTRO DE AYUDA',
            'title' => 'Preguntas frecuentes',
            'subtitle' => 'Resuelve las dudas más comunes.',
            'source' => 'featured',
            'limit' => 6,
            'show_view_all' => true,
            'view_all_label' => 'Ver todas las preguntas →',
        ];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\TextInput::make('kicker')->label('Kicker')->maxLength(60),
                Forms\Components\TextInput::make('title')->label('Título')->maxLength(120),
                Forms\Components\Textarea::make('subtitle')->label('Subtítulo')->rows(2)->maxLength(240),
                Forms\Components\Select::make('source')
                    ->label('Qué preguntas mostrar')
                    ->options([
                        'featured' => 'Solo destacadas',
                        'category' => 'De una categoría específica',
                        'all' => 'Todas (las primeras N)',
                    ])
                    ->default('featured')
                    ->live(),
                Forms\Components\Select::make('category_id')
                    ->label('Categoría')
                    ->options(fn () => FaqCategory::orderBy('sort_order')->pluck('name', 'id'))
                    ->visible(fn ($get) => $get('source') === 'category'),
                Forms\Components\TextInput::make('limit')
                    ->label('Cantidad máxima')
                    ->numeric()
                    ->default(6)
                    ->minValue(1)
                    ->maxValue(20),
                Forms\Components\Toggle::make('show_view_all')->label('Mostrar enlace "Ver todas"')->default(true),
                Forms\Components\TextInput::make('view_all_label')->label('Etiqueta del enlace')->default('Ver todas las preguntas →'),
            ])->columns(2);
    }
}
