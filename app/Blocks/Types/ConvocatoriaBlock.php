<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use App\Models\Convocatoria;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class ConvocatoriaBlock extends BlockType
{
    public static function key(): string { return 'convocatoria'; }
    public static function label(): string { return 'Convocatoria destacada'; }
    public static function icon(): string { return 'heroicon-o-megaphone'; }
    public static function view(): string { return 'blocks.convocatoria'; }

    public static function defaults(): array
    {
        return [
            'convocatoria_id'  => null,
            'hide_when_closed' => true,
            'layout_type'      => 'split',  // split | card | minimal (para proceso) / poster | banner | minimal (para aviso)
        ];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\Select::make('convocatoria_id')
                    ->label('Convocatoria a mostrar')
                    ->options(fn () => Convocatoria::where('status', 'vigente')->pluck('title', 'id'))
                    ->searchable()
                    ->helperText('Si se deja vacio usa la convocatoria vigente mas proxima a cerrar.')
                    ->placeholder('Auto (proxima a cerrar)'),
                Forms\Components\Toggle::make('hide_when_closed')
                    ->label('Ocultar cuando este cerrada')
                    ->helperText('Si esta activo, el bloque desaparece del home cuando la convocatoria cierra. Si esta desactivado, muestra "Convocatoria cerrada" con badge gris.')
                    ->default(true),
            ]);
    }
}
