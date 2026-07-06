<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class HeroBlock extends BlockType
{
    public static function key(): string   { return 'hero'; }
    public static function label(): string { return 'Hero editorial'; }
    public static function icon(): string  { return 'heroicon-o-sparkles'; }
    public static function view(): string  { return 'blocks.hero'; }

    public static function defaults(): array
    {
        return [
            'layout'           => 'editorial',
            'pill'             => 'Aeropuerto operando con normalidad',
            'pill_tone'        => 'success',
            'h1'               => 'Conectar *Guayaquil* con el mundo, con *claridad.*',
            'subtitle'         => '',
            'cta1_label'       => 'Ver estado de vuelos',
            'cta1_url'         => '#',
            'cta2_label'       => 'Portal de transparencia',
            'cta2_url'         => '#',
            'stats'            => [],
            'cards'            => [],
            // Layout: split
            'side_image'       => '',
            // Layout: banner
            'background_image' => '',
            'bg_overlay'       => 'medium',
            'text_align'       => 'center',
            // Layout: centered
            'bg_color'         => 'light',
        ];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                // ── Diseño ──────────────────────────────────────────────────
                Forms\Components\Select::make('layout')
                    ->label('Diseño del hero')
                    ->options([
                        'editorial' => '📰 Editorial — texto izquierda + tarjetas',
                        'centered'  => '✦ Centrado — texto grande, sin tarjetas',
                        'split'     => '▥ Partido — texto izquierda + imagen',
                        'banner'    => '🖼 Banner — fondo de imagen full width',
                    ])
                    ->default('editorial')
                    ->live()
                    ->columnSpanFull(),

                // ── Encabezado ───────────────────────────────────────────────
                Forms\Components\Section::make('Encabezado')
                    ->schema([
                        Forms\Components\TextInput::make('pill')
                            ->label('Pill de estado')
                            ->default('Aeropuerto operando con normalidad'),
                        Forms\Components\Select::make('pill_tone')
                            ->label('Color del pill')
                            ->options([
                                'success' => 'Verde (operativo)',
                                'warn'    => 'Ámbar (precaución)',
                                'neutral' => 'Neutral',
                                'soft'    => 'Azul suave',
                            ])
                            ->default('success'),
                        Forms\Components\Textarea::make('h1')
                            ->label('Titular (usa *palabra* para cursivas)')
                            ->rows(3)
                            ->default('Conectar *Guayaquil* con el mundo, con *claridad.*')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('subtitle')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                // ── Botones ──────────────────────────────────────────────────
                Forms\Components\Section::make('Botones (CTAs)')
                    ->schema([
                        Forms\Components\TextInput::make('cta1_label')->label('Botón primario - etiqueta'),
                        Forms\Components\TextInput::make('cta1_url')->label('Botón primario - URL'),
                        Forms\Components\TextInput::make('cta2_label')->label('Botón secundario - etiqueta'),
                        Forms\Components\TextInput::make('cta2_url')->label('Botón secundario - URL'),
                    ])->columns(2)->collapsed(),

                // ── Métricas ─────────────────────────────────────────────────
                Forms\Components\Section::make('Métricas (cifras)')
                    ->schema([
                        Forms\Components\Repeater::make('stats')
                            ->label('Métricas')
                            ->schema([
                                Forms\Components\TextInput::make('value')->label('Valor')->required(),
                                Forms\Components\TextInput::make('label')->label('Etiqueta')->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(4)
                            ->maxItems(6)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => ($state['value'] ?? '') . ' · ' . ($state['label'] ?? '')),
                    ])->collapsed(),

                // ── Tarjetas (solo layout editorial) ─────────────────────────
                Forms\Components\Section::make('Tarjetas laterales')
                    ->description('Hasta 3 tarjetas: imagen grande (izquierda) + 2 apiladas (derecha).')
                    ->visible(fn (Forms\Get $get) => $get('layout') === 'editorial')
                    ->schema([
                        Forms\Components\Repeater::make('cards')
                            ->label('Tarjetas')
                            ->schema([
                                Forms\Components\Select::make('variant')
                                    ->label('Tipo')
                                    ->options([
                                        'image'   => 'Imagen grande',
                                        'primary' => 'Azul primaria (destacada)',
                                        'surface' => 'Blanca (neutra)',
                                    ])
                                    ->required()
                                    ->default('surface')
                                    ->live(),
                                Forms\Components\TextInput::make('kicker')->label('Kicker'),
                                Forms\Components\TextInput::make('title')->label('Título')->required(),
                                Forms\Components\FileUpload::make('image')
                                    ->label('Imagen')
                                    ->image()
                                    ->directory('hero-cards')
                                    ->disk('public')
                                    ->visible(fn (Forms\Get $get) => $get('variant') === 'image'),
                                Forms\Components\TextInput::make('meta')->label('Meta (ej: fecha)'),
                                Forms\Components\TextInput::make('cta_label')->label('Enlace - etiqueta'),
                                Forms\Components\TextInput::make('cta_url')->label('Enlace - URL'),
                            ])
                            ->columns(2)
                            ->maxItems(3)
                            ->collapsible()
                            ->reorderable()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Nueva tarjeta'),
                    ])->collapsed(),

                // ── Imagen lateral (layout split) ─────────────────────────────
                Forms\Components\Section::make('Imagen lateral')
                    ->visible(fn (Forms\Get $get) => $get('layout') === 'split')
                    ->schema([
                        Forms\Components\FileUpload::make('side_image')
                            ->label('Imagen derecha')
                            ->image()
                            ->directory('hero')
                            ->disk('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),

                // ── Fondo imagen (layout banner) ──────────────────────────────
                Forms\Components\Section::make('Fondo e imagen')
                    ->visible(fn (Forms\Get $get) => $get('layout') === 'banner')
                    ->schema([
                        Forms\Components\FileUpload::make('background_image')
                            ->label('Imagen de fondo')
                            ->image()
                            ->directory('hero')
                            ->disk('public')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('bg_overlay')
                            ->label('Oscuridad del overlay')
                            ->options([
                                'light'  => 'Suave (20%)',
                                'medium' => 'Medio (50%)',
                                'dark'   => 'Fuerte (70%)',
                            ])
                            ->default('medium'),
                        Forms\Components\Select::make('text_align')
                            ->label('Alineación del texto')
                            ->options(['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha'])
                            ->default('center'),
                    ])->columns(2),

                // ── Fondo color (layout centered) ─────────────────────────────
                Forms\Components\Section::make('Fondo')
                    ->visible(fn (Forms\Get $get) => $get('layout') === 'centered')
                    ->schema([
                        Forms\Components\Select::make('bg_color')
                            ->label('Color de fondo')
                            ->options([
                                'light'  => 'Claro (bg)',
                                'soft'   => 'Azul suave',
                                'navy'   => 'Navy oscuro',
                                'gradient' => 'Degradado azul',
                            ])
                            ->default('light'),
                        Forms\Components\Select::make('text_align')
                            ->label('Alineación del texto')
                            ->options(['left' => 'Izquierda', 'center' => 'Centro'])
                            ->default('center'),
                    ])->columns(2),
            ]);
    }
}
