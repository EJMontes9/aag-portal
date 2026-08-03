<?php

namespace App\Blocks\Types;

use App\Blocks\BlockType;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

class QuickLinksBlock extends BlockType
{
    public static function key(): string { return 'quick_links'; }
    public static function label(): string { return 'Accesos directos (iconos)'; }
    public static function icon(): string { return 'heroicon-o-squares-2x2'; }
    public static function view(): string { return 'blocks.quick-links'; }

    public static function defaults(): array
    {
        return [
            'kicker' => 'ACCESOS DIRECTOS',
            'title' => 'Lo que más se consulta',
            'link_all_label' => 'Ver todos los servicios →',
            'link_all_url' => '/servicios',
            'links' => [
                ['icon' => 'plane', 'label' => 'Vuelos', 'description' => 'Llegadas y salidas en tiempo real', 'url' => '/vuelos'],
                ['icon' => 'doc', 'label' => 'Trámites', 'description' => 'Formularios y solicitudes', 'url' => '/tramites'],
                ['icon' => 'building', 'label' => 'Nosotros', 'description' => 'Conoce la AAG', 'url' => '/nosotros'],
                ['icon' => 'phone', 'label' => 'Contacto', 'description' => 'Comunícate con nosotros', 'url' => '/contacto'],
            ],
        ];
    }

    public const ICONS = [
        'plane' => 'Avión',
        'doc' => 'Documento',
        'check' => 'Check',
        'building' => 'Edificio',
        'download' => 'Descarga',
        'phone' => 'Teléfono',
        'envelope' => 'Sobre',
        'user' => 'Usuario',
        'globe' => 'Globo',
        'search' => 'Búsqueda',
    ];

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon(self::icon())
            ->schema([
                Forms\Components\TextInput::make('kicker')->label('Kicker')->default('ACCESOS DIRECTOS'),
                Forms\Components\TextInput::make('title')->label('Título')->default('Lo que más se consulta'),
                Forms\Components\TextInput::make('link_all_label')->label('Enlace "Ver todos"')->default('Ver todos los servicios →'),
                Forms\Components\TextInput::make('link_all_url')->label('URL "Ver todos"')->default('/servicios'),
                Forms\Components\Repeater::make('links')
                    ->label('Accesos')
                    ->schema([
                        Forms\Components\Select::make('icon')->label('Icono')->options(self::ICONS)->default('plane')->required(),
                        Forms\Components\TextInput::make('label')->label('Etiqueta')->required(),
                        Forms\Components\TextInput::make('description')->label('Descripción corta'),
                        Forms\Components\TextInput::make('url')->label('URL')->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(6)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Nuevo acceso'),
            ]);
    }
}
