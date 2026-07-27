<?php

namespace Tests\Unit;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;

/**
 * Las cabeceras de seguridad viven en middleware y no en el .htaccess para que
 * viajen con el repositorio. Estas pruebas verifican que efectivamente salen en
 * la respuesta, porque su ausencia es silenciosa: la pagina carga igual de bien
 * sin ellas y nadie lo nota hasta que hay un incidente.
 */
class CabecerasSeguridadTest extends TestCase
{
    private function responder(string $ruta, bool $seguro = false): Response
    {
        $peticion = Request::create(($seguro ? 'https' : 'http') . '://aag.org.ec' . $ruta);

        return (new SecurityHeaders())->handle($peticion, fn () => new Response('contenido'));
    }

    public function test_impide_que_el_navegador_adivine_el_tipo_de_archivo(): void
    {
        $this->assertSame('nosniff', $this->responder('/admin')->headers->get('X-Content-Type-Options'));
    }

    public function test_impide_que_el_portal_se_empotre_en_otro_sitio(): void
    {
        $this->assertSame('SAMEORIGIN', $this->responder('/admin')->headers->get('X-Frame-Options'));
    }

    public function test_no_filtra_la_direccion_completa_al_navegar_a_otro_dominio(): void
    {
        $this->assertSame(
            'strict-origin-when-cross-origin',
            $this->responder('/admin')->headers->get('Referrer-Policy')
        );
    }

    public function test_desactiva_camara_microfono_y_geolocalizacion(): void
    {
        $politica = $this->responder('/admin')->headers->get('Permissions-Policy');

        $this->assertStringContainsString('camera=()', $politica);
        $this->assertStringContainsString('microphone=()', $politica);
        $this->assertStringContainsString('geolocation=()', $politica);
    }

    public function test_oculta_la_version_de_php(): void
    {
        $this->assertFalse($this->responder('/admin')->headers->has('X-Powered-By'));
    }

    /**
     * HSTS solo debe enviarse bajo HTTPS: por HTTP no tiene efecto y en un
     * entorno sin certificado dejaria el dominio inaccesible durante el max-age.
     */
    public function test_solo_exige_https_permanente_cuando_la_peticion_ya_es_segura(): void
    {
        $this->assertFalse(
            $this->responder('/nosotros', seguro: false)->headers->has('Strict-Transport-Security'),
            'No debe enviarse HSTS por HTTP.'
        );

        $this->assertStringContainsString(
            'max-age=31536000',
            $this->responder('/nosotros', seguro: true)->headers->get('Strict-Transport-Security')
        );
    }

    public function test_el_portal_publico_declara_politica_de_contenido(): void
    {
        $politica = $this->responder('/nosotros')->headers->get('Content-Security-Policy');

        $this->assertNotNull($politica, 'El portal publico debe declarar Content-Security-Policy.');
        $this->assertStringContainsString("default-src 'self'", $politica);
        $this->assertStringContainsString("object-src 'none'", $politica);
        $this->assertStringContainsString("frame-ancestors 'self'", $politica);
        $this->assertStringContainsString("form-action 'self'", $politica);
    }

    /**
     * En el panel la politica restrictiva rompe Filament, que genera sus
     * propios scripts. La excepcion es deliberada y conviene dejarla fijada.
     */
    public function test_el_panel_administrativo_queda_excluido_de_la_politica_de_contenido(): void
    {
        $this->assertNull($this->responder('/admin')->headers->get('Content-Security-Policy'));
        $this->assertNull($this->responder('/admin/pages')->headers->get('Content-Security-Policy'));
    }
}
