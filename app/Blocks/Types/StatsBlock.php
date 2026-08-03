<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class StatsBlock extends BlockType
{
    public static function key(): string { return 'stats'; }
    public static function label(): string { return 'Números / Estadísticas'; }
    public static function icon(): string { return 'heroicon-o-chart-bar'; }
    public static function view(): string { return 'blocks.stats'; }

    public static function defaults(): array
    {
        return [
            'kicker' => 'EN NÚMEROS',
            'title' => 'La AAG en cifras',
            'subtitle' => 'Datos representativos de la operación del Aeropuerto Internacional José Joaquín de Olmedo.',
            'background' => 'soft',
            'items' => [
                ['value' => '8.2M', 'label' => 'Pasajeros al año'],
                ['value' => '92', 'label' => 'Destinos conectados'],
                ['value' => '15', 'label' => 'Aerolíneas operando'],
                ['value' => '99.8%', 'label' => 'Disponibilidad operativa'],
            ],
        ];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\TextInput::make('kicker')->label('Kicker'),
                Forms\Components\TextInput::make('title')->label('Título'),
                Forms\Components\Textarea::make('subtitle')->label('Descripción')->rows(2),
                Forms\Components\Repeater::make('items')
                    ->label('Estadísticas')
                    ->schema([
                        Forms\Components\TextInput::make('value')->label('Valor')->required(),
                        Forms\Components\TextInput::make('label')->label('Etiqueta')->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(4)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => ($state['value'] ?? '').' · '.($state['label'] ?? '')),
                Forms\Components\Select::make('background')
                    ->label('Fondo')
                    ->options(['bg' => 'Claro', 'soft' => 'Azul suave', 'navy' => 'Navy'])
                    ->default('bg'),
            ])->columns(2);
    }
}
