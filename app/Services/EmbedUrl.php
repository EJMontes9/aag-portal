<?php

namespace App\Services;

/**
 * Valida las direcciones de contenido incrustado (mapas y video).
 *
 * POR QUE
 * -------
 * Los bloques de mapa y de texto+imagen aceptaban un fragmento de HTML que se
 * pintaba tal cual con {!! !!}. El unico "saneo" era quitar los atributos
 * width, height y style, lo cual es cosmetico: un <script> o un
 * <iframe onload="..."> pegados en ese campo se ejecutaban en el portal.
 *
 * En lugar de intentar limpiar HTML arbitrario, aqui se hace lo contrario: se
 * extrae UNICAMENTE la direccion, se comprueba que sea de un proveedor
 * conocido, y el <iframe> lo construye la plantilla. Asi da igual lo que
 * peguen en el campo; lo unico que sobrevive es una URL de la lista.
 *
 * Admite las dos formas de rellenar el campo, porque los administradores
 * suelen pegar el "codigo para insertar" completo:
 *   - el fragmento <iframe src="..."></iframe> de Google Maps o YouTube
 *   - o solo la direccion
 */
class EmbedUrl
{
    /**
     * Dominios permitidos por tipo de incrustacion.
     * Se compara el host COMPLETO o un subdominio suyo, nunca por "contiene":
     * "google.com.atacante.net" no debe colarse.
     */
    protected const PERMITIDOS = [
        'mapa' => [
            'google.com',
            'maps.google.com',
            'www.google.com',
            'openstreetmap.org',
            'www.openstreetmap.org',
        ],
        'video' => [
            'youtube.com',
            'www.youtube.com',
            'youtube-nocookie.com',
            'www.youtube-nocookie.com',
            'player.vimeo.com',
            'vimeo.com',
        ],
    ];

    /**
     * Devuelve una URL segura para incrustar, o null si no la hay.
     *
     * @param string|null $entrada  URL suelta o fragmento HTML con un iframe
     * @param string      $tipo     'mapa' o 'video'
     */
    public static function extraer(?string $entrada, string $tipo = 'mapa'): ?string
    {
        $entrada = trim((string) $entrada);

        if ($entrada === '') {
            return null;
        }

        // Si viene un iframe pegado, quedarse solo con su src.
        if (preg_match('/<iframe[^>]+src\s*=\s*["\']([^"\']+)["\']/i', $entrada, $m)) {
            $url = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
        } else {
            $url = html_entity_decode($entrada, ENT_QUOTES | ENT_HTML5);
        }

        $url = trim($url);

        // Solo https. Deja fuera javascript:, data: y el http sin cifrar, que
        // ademas provocaria un aviso de contenido mixto en el navegador.
        if (! preg_match('#^https://#i', $url)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '') {
            return null;
        }

        foreach (self::PERMITIDOS[$tipo] ?? [] as $permitido) {
            // Coincidencia exacta o subdominio real ("algo.google.com"),
            // nunca por substring.
            if ($host === $permitido || str_ends_with($host, '.' . $permitido)) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Lista de dominios permitidos, para mostrarla como ayuda en el panel.
     */
    public static function dominiosPermitidos(string $tipo = 'mapa'): string
    {
        return implode(', ', self::PERMITIDOS[$tipo] ?? []);
    }
}
