<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class ValuesBlock extends BlockType
{
    public static function key(): string { return 'values'; }
    public static function label(): string { return 'Valores institucionales (01, 02...)'; }
    public static function icon(): string { return 'heroicon-o-heart'; }
    public static function view(): string { return 'blocks.values'; }

    public static function defaults(): array
    {
        return [
            'kicker' => 'NUESTROS VALORES',
            'title' => 'Un aeropuerto *cercano*, una gestión *institucional.*',
            'subtitle' => 'Trabajamos para que cada pasajero que pisa el José Joaquín de Olmedo se sienta en casa, y para que cada ciudadano pueda verificar cómo se administra lo público.',
            'items' => [
                ['number' => '01', 'title' => 'Eficiencia', 'description' => 'Procesos ágiles que respetan el tiempo del viajero y del ciudadano.'],
                ['number' => '02', 'title' => 'Empatía', 'description' => 'Un aeropuerto hecho para las personas, no para los procedimientos.'],
                ['number' => '03', 'title' => 'Calidad', 'description' => 'Estándares internacionales aplicados a la operación diaria.'],
                ['number' => '04', 'title' => 'Transparencia', 'description' => 'Información pública abierta, actualizada y verificable.'],
            ],
        ];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\TextInput::make('kicker')->label('Kicker')->default('NUESTROS VALORES'),
                Forms\Components\Textarea::make('title')->label('Título (usa *cursivas*)')->rows(2)->default('Un aeropuerto *cercano*, una gestión *institucional.*'),
                Forms\Components\Textarea::make('subtitle')->label('Descripción')->rows(2),
                Forms\Components\Repeater::make('items')
                    ->label('Valores')
                    ->schema([
                        Forms\Components\TextInput::make('number')->label('Número')->maxLength(4)->default('01')->required(),
                        Forms\Components\TextInput::make('title')->label('Título')->required(),
                        Forms\Components\Textarea::make('description')->label('Descripción')->rows(2)->required(),
                    ])
                    ->columns(3)
                    ->defaultItems(4)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => ($state['number'] ?? '').' · '.($state['title'] ?? '')),
            ]);
    }
}
