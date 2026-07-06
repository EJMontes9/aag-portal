<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class CtaBlock extends BlockType
{
    public static function key(): string { return 'cta'; }
    public static function label(): string { return 'Llamado a la accion (CTA)'; }
    public static function icon(): string { return 'heroicon-o-cursor-arrow-rays'; }
    public static function view(): string { return 'blocks.cta'; }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\TextInput::make('title')->label('Titulo')->required(),
                Forms\Components\Textarea::make('subtitle')->label('Descripcion')->rows(2),
                Forms\Components\TextInput::make('cta_label')->label('Boton - etiqueta')->required(),
                Forms\Components\TextInput::make('cta_url')->label('Boton - URL')->required(),
                Forms\Components\Select::make('background')
                    ->label('Estilo del bloque')
                    ->options([
                        'navy' => 'Fondo navy (destacado)',
                        'primary' => 'Fondo azul primario',
                        'soft' => 'Fondo azul suave',
                        'card' => 'Blanco con borde',
                    ])
                    ->default('navy')
                    ->required(),
                Forms\Components\Select::make('align')
                    ->label('Alineacion')
                    ->options(['left' => 'Izquierda', 'center' => 'Centrado'])
                    ->default('center'),
            ])->columns(2);
    }
}
