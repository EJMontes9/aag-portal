<?php

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

if (! function_exists('settings')) {
    function settings(string $key, mixed $default = null): mixed
    {
        return SiteSetting::get($key, $default);
    }
}

if (! function_exists('setting_asset')) {
    function setting_asset(string $key, ?string $default = null): ?string
    {
        $path = SiteSetting::get($key);
        // Algunos registros viejos pueden estar guardados como array (FileUpload single).
        if (is_array($path)) {
            $path = reset($path) ?: null;
        }
        if (! $path || ! is_string($path)) {
            return $default;
        }
        return Storage::disk('public')->url($path);
    }
}

/**
 * Convierte un color hex (#0B1E4A) al formato "R G B" que Tailwind usa
 * con sintaxis rgb(var(--color-xxx) / <alpha-value>).
 * Si recibe algo invalido, devuelve el fallback.
 */
/**
 * Convierte texto con *palabras* en cursivas HTML <em>palabras</em>.
 * Escapa el resto para evitar XSS.
 */
if (! function_exists('italic_markdown')) {
    function italic_markdown(?string $text): string
    {
        if (! $text) return '';
        $escaped = e($text);
        return preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $escaped);
    }
}

/**
 * Igual que italic_markdown pero envuelve cada palabra en un <span data-word>
 * para poder animarlas con stagger desde JS. Mantiene saltos de linea.
 * Usar en bloques con titulares animables (ej: hero h1).
 */
if (! function_exists('italic_markdown_words')) {
    function italic_markdown_words(?string $text): string
    {
        if (! $text) return '';
        $lines = explode("\n", $text);
        $rendered = [];
        foreach ($lines as $line) {
            $escaped = e($line);
            $with_em = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $escaped);
            $wrapped = preg_replace_callback(
                '/(<em>[^<]+<\/em>|\S+)/u',
                fn ($m) => '<span data-word class="inline-block will-change-transform">'.$m[0].'</span>',
                $with_em
            );
            $rendered[] = $wrapped;
        }
        return implode('<br>', $rendered);
    }
}

/**
 * Detecta el formato de un valor de metrica y lo prepara para animar.
 * "8.2M"  -> ['target' => 8.2,  'format' => 'compact', 'suffix' => 'M', 'decimals' => 1]
 * "97%"   -> ['target' => 97,   'format' => 'percent', 'decimals' => 0]
 * "22"    -> ['target' => 22,   'format' => 'integer']
 * Devuelve null si no es animable.
 */
if (! function_exists('parse_stat_value_for_animation')) {
    function parse_stat_value_for_animation(?string $value): ?array
    {
        if (! $value) return null;
        $v = trim($value);

        if (preg_match('/^(\d+(?:\.\d+)?)([MK])$/i', $v, $m)) {
            $num = (float) $m[1];
            $suffix = strtoupper($m[2]);
            $multiplier = $suffix === 'M' ? 1_000_000 : 1_000;
            return [
                'target' => $num * $multiplier,
                'format' => 'compact',
                'decimals' => str_contains($m[1], '.') ? 1 : 0,
            ];
        }

        if (preg_match('/^(\d+(?:\.\d+)?)\s*%$/', $v, $m)) {
            return [
                'target' => (float) $m[1],
                'format' => 'percent',
                'decimals' => str_contains($m[1], '.') ? 1 : 0,
            ];
        }

        if (preg_match('/^\d+$/', $v)) {
            return ['target' => (int) $v, 'format' => 'integer'];
        }

        return null;
    }
}

if (! function_exists('hex_to_rgb_tuple')) {
    function hex_to_rgb_tuple(?string $hex, string $fallback = '0 0 0'): string
    {
        if (! $hex) {
            return $fallback;
        }
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (! preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            return $fallback;
        }
        return hexdec(substr($hex, 0, 2)).' '.hexdec(substr($hex, 2, 2)).' '.hexdec(substr($hex, 4, 2));
    }
}

/**
 * Devuelve el tuple "R G B" de texto (blanco o navy oscuro) que mejor
 * contrasta sobre un color de fondo dado (luminancia relativa WCAG).
 * Uso: color de texto para botones/insignias sobre --color-accent, que
 * puede ser configurado por el admin como un color claro (amarillo) o
 * oscuro (azul) -- el texto se adapta solo para mantener buen contraste.
 */
if (! function_exists('contrast_text_tuple')) {
    function contrast_text_tuple(?string $hex, string $dark = '11 30 74', string $light = '255 255 255'): string
    {
        $hex = ltrim((string) $hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (! preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            return $dark;
        }
        // Luminancia relativa aproximada (formula WCAG simplificada).
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        return $luminance > 0.55 ? $dark : $light;
    }
}
