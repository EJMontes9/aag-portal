<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class VideoBlock extends BlockType
{
    public static function key(): string   { return 'video'; }
    public static function label(): string { return 'Video (YouTube/Vimeo)'; }
    public static function icon(): string  { return 'heroicon-o-play-circle'; }
    public static function view(): string  { return 'blocks.video'; }

    public static function defaults(): array
    {
        return [
            'kicker'         => '',
            'title'          => '',
            'subtitle'       => '',
            'video_url'      => '',
            'width'          => 'lg',
            'background'     => 'bg',
            'autoplay'       => false,
            'mute'           => false,
            'loop'           => false,
            'controls'       => true,
            'start_min'      => 0,
            'start_sec'      => 0,
            'rel'            => false,
            'modestbranding' => true,
        ];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\TextInput::make('kicker')->label('Kicker (opcional)'),
                Forms\Components\TextInput::make('title')->label('Titulo')->required(),
                Forms\Components\Textarea::make('subtitle')->label('Descripción')->rows(2)->columnSpanFull(),

                Forms\Components\TextInput::make('video_url')
                    ->label('URL del video')
                    ->helperText('YouTube: https://youtu.be/XXXX o https://youtube.com/watch?v=XXXX | Vimeo: https://vimeo.com/XXXXX')
                    ->required()
                    ->url()
                    ->columnSpanFull(),

                Forms\Components\Section::make('Inicio en tiempo específico')
                    ->description('Deja en 0 para iniciar desde el principio del video.')
                    ->schema([
                        Forms\Components\TextInput::make('start_min')
                            ->label('Minutos')->numeric()->minValue(0)->default(0),
                        Forms\Components\TextInput::make('start_sec')
                            ->label('Segundos (0–59)')->numeric()->minValue(0)->maxValue(59)->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Comportamiento del reproductor')
                    ->schema([
                        Forms\Components\Toggle::make('autoplay')
                            ->label('Reproducción automática al cargar')
                            ->helperText('Al activar, el video se silencia automáticamente (requisito de los navegadores).')
                            ->default(false)
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $state ? $set('mute', true) : null),

                        Forms\Components\Toggle::make('mute')
                            ->label('Silenciado al inicio')
                            ->helperText('El visitante puede activar el sonido manualmente.')
                            ->default(false),

                        Forms\Components\Toggle::make('loop')
                            ->label('Repetir en bucle')
                            ->helperText('El video vuelve a empezar cuando termina.')
                            ->default(false),

                        Forms\Components\Toggle::make('controls')
                            ->label('Mostrar barra de controles')
                            ->helperText('Si se desactiva, el visitante no puede pausar ni cambiar volumen.')
                            ->default(true),

                        Forms\Components\Toggle::make('rel')
                            ->label('Mostrar videos relacionados al terminar')
                            ->helperText('Solo aplica a YouTube. Desactívalo para evitar distracciones.')
                            ->default(false),

                        Forms\Components\Toggle::make('modestbranding')
                            ->label('Ocultar logo de YouTube en la barra de controles')
                            ->default(true),
                    ])->columns(2)->columnSpanFull(),

                Forms\Components\Select::make('width')
                    ->label('Ancho del video')
                    ->options([
                        'sm'   => 'Pequeño',
                        'md'   => 'Mediano',
                        'lg'   => 'Grande (por defecto)',
                        'full' => 'Ancho completo',
                    ])
                    ->default('lg'),

                Forms\Components\Select::make('background')
                    ->label('Fondo de la sección')
                    ->options([
                        'bg'   => 'Normal (claro)',
                        'soft' => 'Azul suave',
                        'navy' => 'Navy (oscuro)',
                    ])
                    ->default('bg'),
            ])->columns(2);
    }
}
