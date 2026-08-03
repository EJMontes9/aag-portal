<?php

namespace App\Blocks;

class BlockRegistry
{
    /**
     * Registro central de los tipos de bloque disponibles.
     * Para agregar un nuevo tipo solo crea la clase y regístrala aquí.
     */
    public static function types(): array
    {
        return [
            Types\HeroBlock::class,
            Types\BannerSliderBlock::class,
            Types\QuickLinksBlock::class,
            Types\NewsHighlightsBlock::class,
            Types\FlightsBlock::class,
            Types\ConvocatoriaBlock::class,
            Types\ValuesBlock::class,
            Types\VideoBlock::class,
            Types\TextImageBlock::class,
            Types\CtaBlock::class,
            Types\StatsBlock::class,
            Types\FaqAccordionBlock::class,
            Types\TransparencyBrowserBlock::class,
            Types\FormBlock::class,
            Types\MapBlock::class,
        ];
    }

    public static function filamentBlocks(): array
    {
        return array_map(fn ($class) => $class::filamentBlock(), self::types());
    }

    public static function viewFor(string $typeKey): ?string
    {
        foreach (self::types() as $class) {
            if ($class::key() === $typeKey) {
                return $class::view();
            }
        }
        return null;
    }
}
