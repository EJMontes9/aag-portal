<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class BannerSliderBlock extends BlockType
{
    public static function key(): string { return 'banner_slider'; }
    public static function label(): string { return 'Banner rotativo (slider)'; }
    public static function icon(): string { return 'heroicon-o-photo'; }
    public static function view(): string { return 'blocks.banner-slider'; }

    public static function defaults(): array
    {
        return [
            'autoplay' => true,
            'interval' => 6,
            'show_indicators' => true,
            'show_arrows' => true,
            'height' => 'medium',
            'slides' => [
                [
                    'title' => 'Bienvenido al Aeropuerto José Joaquín de Olmedo',
                    'subtitle' => 'Conectando Guayaquil con el mundo desde 1962.',
                    'cta_label' => 'Conoce más',
                    'cta_url' => '/nosotros',
                    'image' => null,
                    'overlay' => 'medium',
                    'align' => 'left',
                ],
                [
                    'title' => 'Transparencia y rendición de cuentas',
                    'subtitle' => 'Información pública abierta y verificable.',
                    'cta_label' => 'Ver portal de transparencia',
                    'cta_url' => '/transparencia',
                    'image' => null,
                    'overlay' => 'medium',
                    'align' => 'left',
                ],
                [
                    'title' => 'Convocatorias abiertas',
                    'subtitle' => 'Postula a los procesos de selección vigentes.',
                    'cta_label' => 'Ver convocatorias',
                    'cta_url' => '/convocatorias',
                    'image' => null,
                    'overlay' => 'medium',
                    'align' => 'left',
                ],
            ],
        ];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\Section::make('Comportamiento')
                    ->schema([
                        Forms\Components\Toggle::make('autoplay')
                            ->label('Rotación automática')
                            ->default(true),
                        Forms\Components\TextInput::make('interval')
                            ->label('Intervalo entre slides (segundos)')
                            ->numeric()
                            ->minValue(2)
                            ->maxValue(30)
                            ->default(6),
                        Forms\Components\Toggle::make('show_indicators')
                            ->label('Mostrar indicadores (puntos)')
                            ->default(true),
                        Forms\Components\Toggle::make('show_arrows')
                            ->label('Mostrar flechas de navegación')
                            ->default(true),
                        Forms\Components\Select::make('height')
                            ->label('Altura')
                            ->options([
                                'small' => 'Pequeño (50vh)',
                                'medium' => 'Mediano (70vh)',
                                'large' => 'Grande (85vh)',
                                'full' => 'Pantalla completa (100vh)',
                            ])
                            ->default('medium'),
                    ])->columns(2)->collapsed(),

                Forms\Components\Section::make('Slides')
                    ->description('Imágenes y textos que rotan. Recomendado: 3-5 slides. Resolución ideal 1920x1080px.')
                    ->schema([
                        Forms\Components\Repeater::make('slides')
                            ->label('Slides')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('Imagen de fondo')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                    ->imageEditor()
                                    ->directory('banners')
                                    ->disk('public')
                                    ->maxSize(4096)
                                    ->helperText('JPG/PNG, máximo 4MB. Recomendado 1920x1080.'),
                                Forms\Components\TextInput::make('title')
                                    ->label('Título principal')
                                    ->required()
                                    ->maxLength(120),
                                Forms\Components\Textarea::make('subtitle')
                                    ->label('Subtítulo / descripción corta')
                                    ->rows(2)
                                    ->maxLength(240),
                                Forms\Components\TextInput::make('cta_label')
                                    ->label('Botón - etiqueta')
                                    ->maxLength(40),
                                Forms\Components\TextInput::make('cta_url')
                                    ->label('Botón - URL')
                                    ->maxLength(255),
                                Forms\Components\Select::make('overlay')
                                    ->label('Overlay oscuro')
                                    ->options([
                                        'none' => 'Sin overlay',
                                        'light' => 'Suave (30%)',
                                        'medium' => 'Medio (50%)',
                                        'strong' => 'Fuerte (70%)',
                                    ])
                                    ->default('medium')
                                    ->helperText('Mejora legibilidad del texto sobre la imagen.'),
                                Forms\Components\Select::make('align')
                                    ->label('Alineación del texto')
                                    ->options([
                                        'left' => 'Izquierda',
                                        'center' => 'Centro',
                                        'right' => 'Derecha',
                                    ])
                                    ->default('left'),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->maxItems(8)
                            ->defaultItems(3)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Nuevo slide'),
                    ]),
            ]);
    }
}
