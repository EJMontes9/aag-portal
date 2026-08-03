<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;

/**
 * Contrato para cada tipo de bloque del Page Builder.
 * Cada clase define:
 *  - key():          identificador único ('hero', 'video', etc.)
 *  - label():        nombre visible en el selector
 *  - icon():         heroicon para el builder
 *  - filamentBlock(): retorna un Block Filament con los campos
 *  - view():         path Blade para renderizar
 *  - defaults():     valores por defecto al crear un bloque nuevo
 */
abstract class BlockType
{
    abstract public static function key(): string;
    abstract public static function label(): string;
    abstract public static function icon(): string;
    abstract public static function filamentBlock(): Block;
    abstract public static function view(): string;

    public static function defaults(): array
    {
        return [];
    }
}
