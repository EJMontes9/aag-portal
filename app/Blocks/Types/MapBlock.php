<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class MapBlock extends BlockType
{
    public static function key(): string   { return 'map'; }
    public static function label(): string { return 'Mapa interactivo'; }
    public static function icon(): string  { return 'heroicon-o-map-pin'; }
    public static function view(): string  { return 'blocks.map'; }

    public static function defaults(): array
    {
        return [
            'title'      => '',
            'embed_code' => '',
            'height'     => 'medium',
            'background' => 'bg',
        ];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título de la sección (opcional)')
                    ->maxLength(120),

                Forms\Components\Textarea::make('embed_code')
                    ->label('Código embed de Google Maps')
                    ->placeholder('<iframe src="https://www.google.com/maps/embed?pb=..." width="600" height="450" ...></iframe>')
                    ->helperText('Google Maps → Compartir → Insertar un mapa → copia el código iframe.')
                    ->rows(4)
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('height')
                    ->label('Altura del mapa')
                    ->options([
                        'small'  => 'Pequeño (300px)',
                        'medium' => 'Mediano (450px)',
                        'large'  => 'Grande (600px)',
                        'full'   => 'Pantalla completa',
                    ])
                    ->default('medium'),

                Forms\Components\Select::make('background')
                    ->label('Fondo de la sección')
                    ->options([
                        'bg'   => 'Claro',
                        'soft' => 'Azul suave',
                        'card' => 'Blanco',
                    ])
                    ->default('bg'),
            ])->columns(2);
    }
}
