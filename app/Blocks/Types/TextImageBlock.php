<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class TextImageBlock extends BlockType
{
    public static function key(): string { return 'text_image'; }
    public static function label(): string { return 'Texto + Imagen'; }
    public static function icon(): string { return 'heroicon-o-photo'; }
    public static function view(): string { return 'blocks.text-image'; }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\TextInput::make('kicker')->label('Kicker (opcional)'),
                Forms\Components\TextInput::make('title')->label('Título')->required(),
                Forms\Components\Textarea::make('body')->label('Texto principal')->rows(5),
                Forms\Components\FileUpload::make('image')
                    ->label('Imagen')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->maxSize(4096)
                    ->directory('blocks')
                    ->disk('public')
                    ->imageEditor()
                    ->helperText('Si prefieres mostrar un mapa, déjalo vacío y pega el embed de Google Maps abajo.'),

                Forms\Components\Textarea::make('map_embed')
                    ->label('Embed de Google Maps (opcional)')
                    ->rows(3)
                    ->placeholder('<iframe src="https://www.google.com/maps/embed?pb=..." width="600" height="450" ...></iframe>')
                    ->helperText('Ve a Google Maps → Compartir → Insertar mapa → copia el código iframe. Si hay imagen y mapa, se muestra la imagen.')
                    ->columnSpanFull(),

                Forms\Components\Select::make('image_side')
                    ->label('Lado de la imagen')
                    ->options(['right' => 'Derecha', 'left' => 'Izquierda'])
                    ->default('right'),
                Forms\Components\TextInput::make('cta_label')->label('Botón - etiqueta'),
                Forms\Components\TextInput::make('cta_url')->label('Botón - URL'),
                Forms\Components\Select::make('background')
                    ->label('Fondo')
                    ->options([
                        'bg' => 'Normal (claro)',
                        'soft' => 'Azul suave',
                        'card' => 'Blanco destacado',
                    ])
                    ->default('bg'),
            ])->columns(2);
    }
}
