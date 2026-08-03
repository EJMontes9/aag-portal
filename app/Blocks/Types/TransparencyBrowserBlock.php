<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class TransparencyBrowserBlock extends BlockType
{
    public static function key(): string { return 'transparency_browser'; }
    public static function label(): string { return 'Navegador de Transparencia (LOTAIP / Rendición)'; }
    public static function icon(): string { return 'heroicon-o-archive-box'; }
    public static function view(): string { return 'blocks.transparency-browser'; }

    public static function defaults(): array
    {
        return [
            'section' => 'lotaip',
            'kicker' => 'TRANSPARENCIA',
            'title' => 'Información pública · LOTAIP',
            'intro' => 'En cumplimiento de la Ley Orgánica de Transparencia y Acceso a la Información Pública (LOTAIP), publicamos la información institucional mínima conforme al Art. 7.',
        ];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\Select::make('section')
                    ->label('Sección a mostrar')
                    ->options([
                        'lotaip' => 'LOTAIP / Transparencia',
                        'rendicion' => 'Rendición de cuentas',
                    ])
                    ->default('lotaip')
                    ->required(),
                Forms\Components\TextInput::make('kicker')->label('Kicker')->maxLength(60),
                Forms\Components\TextInput::make('title')->label('Título')->maxLength(160),
                Forms\Components\Textarea::make('intro')
                    ->label('Texto introductorio')
                    ->rows(4)
                    ->maxLength(1000)
                    ->helperText('Se muestra arriba del navegador de años/meses. Soporta saltos de línea.'),
            ])->columns(2);
    }
}
