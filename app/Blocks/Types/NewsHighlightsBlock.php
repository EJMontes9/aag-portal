<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class NewsHighlightsBlock extends BlockType
{
    public static function key(): string { return 'news_highlights'; }
    public static function label(): string { return 'Noticias destacadas'; }
    public static function icon(): string { return 'heroicon-o-newspaper'; }
    public static function view(): string { return 'blocks.news-highlights'; }

    public static function defaults(): array
    {
        return [
            'kicker' => 'SALA DE PRENSA',
            'title' => 'Últimas noticias',
            'subtitle' => 'Comunicados oficiales y novedades del aeropuerto.',
            'limit' => 3,
            'source' => 'featured', // featured | latest
            'show_view_all' => true,
            'view_all_label' => 'Ver todas las noticias →',
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
                    ->label('Qué noticias mostrar')
                    ->options([
                        'featured' => 'Solo destacadas (marcadas en el panel)',
                        'latest' => 'Las más recientes',
                    ])
                    ->default('featured')
                    ->required(),
                Forms\Components\Select::make('limit')
                    ->label('Cantidad a mostrar')
                    ->options([2 => '2', 3 => '3', 4 => '4', 6 => '6'])
                    ->default(3)
                    ->required(),
                Forms\Components\Toggle::make('show_view_all')
                    ->label('Mostrar enlace "Ver todas"')
                    ->default(true),
                Forms\Components\TextInput::make('view_all_label')
                    ->label('Etiqueta del enlace')
                    ->default('Ver todas las noticias →'),
            ])->columns(2);
    }
}
