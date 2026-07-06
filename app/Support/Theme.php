<?php

namespace App\Support;

/**
 * Catálogo cerrado de temas visuales instalables/activables desde Filament.
 *
 * Un "tema" controla SOLO dos cosas:
 *  1) El layout estructural de header/footer (cómo se ordenan las cosas).
 *  2) Un puñado de "tokens de estilo" (radios de borde, densidad de espaciado,
 *     degradados, elevación/sombras) que alimentan las mismas clases utilitarias
 *     que ya usan todos los bloques (card-surface, rounded-card, section-wrap...).
 *
 * Color y tipografía NO son parte del tema: siguen siendo configuración global
 * en SiteSetting, independiente de cuál tema esté activo (decisión de producto).
 *
 * Para añadir un tercer tema en el futuro: agregar su slug a CATALOG, crear
 * resources/views/components/layout/themes/{slug}/header.blade.php y footer.blade.php,
 * y listo — el resto (tokens, selector en Filament) ya es genérico.
 */
class Theme
{
    public const DEFAULT = 'institucional';

    /** Catálogo de temas disponibles en el código (no confundir con "activo"). */
    public const CATALOG = [
        'institucional' => [
            'label' => 'Institucional (actual)',
            'description' => 'El diseño que ya está en producción: navegación editorial, tarjetas suaves y esquinas redondeadas.',
            'defaults' => [
                'radius' => 'soft',
                'density' => 'comfortable',
                'gradients' => true,
                'elevation' => 'soft',
            ],
        ],
        'corporativo' => [
            'label' => 'Corporativo',
            'description' => 'Esquema corporativo institucional de la AAG: topbar de utilidades, navegación en mayúsculas a lo ancho y tarjetas planas con bordes cuadrados.',
            'defaults' => [
                'radius' => 'square',
                'density' => 'compact',
                'gradients' => false,
                'elevation' => 'flat',
            ],
        ],
    ];

    /** Slugs válidos, p. ej. ['institucional', 'corporativo']. */
    public static function slugs(): array
    {
        return array_keys(self::CATALOG);
    }

    /** ¿Este tema está habilitado (visible/seleccionable) según SiteSetting? */
    public static function isEnabled(string $slug): bool
    {
        if (! array_key_exists($slug, self::CATALOG)) {
            return false;
        }

        return (bool) settings("theme_{$slug}_enabled", true);
    }

    /** Lista de slugs actualmente habilitados. */
    public static function enabledSlugs(): array
    {
        return array_values(array_filter(self::slugs(), fn ($slug) => self::isEnabled($slug)));
    }

    /**
     * Tema activo, validado: si el guardado no existe o fue desinstalado
     * (deshabilitado), cae de vuelta al institucional para nunca romper el sitio.
     */
    public static function active(): string
    {
        $active = settings('theme_active', self::DEFAULT);

        if (is_string($active) && self::isEnabled($active)) {
            return $active;
        }

        return self::isEnabled(self::DEFAULT) ? self::DEFAULT : (self::enabledSlugs()[0] ?? self::DEFAULT);
    }

    public static function is(string $slug): bool
    {
        return self::active() === $slug;
    }

    /**
     * Token de estilo del tema activo (o de un tema específico si se indica).
     * Lee la clave `theme_{slug}_{key}` de SiteSetting, con fallback al
     * default declarado en el catálogo y luego al $default explícito.
     */
    public static function token(string $key, mixed $default = null, ?string $slug = null): mixed
    {
        $slug = $slug ?? self::active();
        $catalogDefault = self::CATALOG[$slug]['defaults'][$key] ?? $default;

        return settings("theme_{$slug}_{$key}", $catalogDefault);
    }

    /** Todos los tokens de estilo del tema activo, listos para inyectar como data-* attrs. */
    public static function activeTokens(): array
    {
        return [
            'radius' => self::token('radius', 'soft'),
            'density' => self::token('density', 'comfortable'),
            'gradients' => (bool) self::token('gradients', true),
            'elevation' => self::token('elevation', 'soft'),
        ];
    }
}
