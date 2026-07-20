<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class FlightsBlock extends BlockType
{
    public static function key(): string { return 'flights'; }
    public static function label(): string { return 'Vuelos en tiempo real'; }
    public static function icon(): string { return 'heroicon-o-paper-airplane'; }
    public static function view(): string { return 'blocks.flights'; }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\TextInput::make('kicker')->label('Kicker')->default('ESTADO DE VUELOS'),
                Forms\Components\TextInput::make('title')->label('Titulo')->default('Consulta llegadas y salidas en tiempo real.'),
                Forms\Components\Textarea::make('subtitle')->label('Descripcion')->rows(2),
                Forms\Components\TextInput::make('cta_label')->label('Etiqueta boton')->default('Ir al portal de vuelos'),
                Forms\Components\TextInput::make('cta_url')->label('URL externa')->default('https://tagsa.aero/vuelos'),
                Forms\Components\TextInput::make('cta_note')->label('Nota junto al boton'),
                Forms\Components\FileUpload::make('image')
                    ->label('Imagen del bloque')
                    ->helperText('Si no se define, se usa la imagen del aeropuerto por defecto.')
                    ->image()
                    // Lista explicita: la regla "image" a secas admite SVG, que
                    // puede llevar <script> dentro y se sirve en nuestro dominio.
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(4096)
                    ->disk('public')
                    ->directory('bloques')
                    ->imageEditor()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('image_alt')
                    ->label('Texto alternativo de la imagen')
                    ->helperText('Describe la imagen para quien no puede verla.')
                    ->columnSpanFull(),
            ])->columns(2);
    }
}
