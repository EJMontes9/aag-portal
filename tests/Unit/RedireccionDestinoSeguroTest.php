<?php

namespace Tests\Unit;

use App\Http\Middleware\RedirigirRutasAntiguas;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * El destino de una redireccion lo escribe una persona desde el panel. Si una
 * cuenta se viera comprometida, poder redirigir a cualquier sitio convertiria
 * el dominio institucional en un trampolin de phishing: un enlace que empieza
 * por aag.org.ec y termina en una copia falsa de un banco.
 *
 * Estas pruebas fijan por escrito que solo se admiten rutas internas y
 * direcciones https, para que nadie relaje la comprobacion sin darse cuenta.
 */
class RedireccionDestinoSeguroTest extends TestCase
{
    private function destinoSeguro(string $destino): bool
    {
        $metodo = new ReflectionMethod(RedirigirRutasAntiguas::class, 'destinoSeguro');
        $metodo->setAccessible(true);

        return $metodo->invoke(new RedirigirRutasAntiguas(), $destino);
    }

    #[DataProvider('destinosAdmitidos')]
    public function test_admite_rutas_internas_y_direcciones_https(string $destino): void
    {
        $this->assertTrue($this->destinoSeguro($destino), "Deberia admitirse el destino: {$destino}");
    }

    #[DataProvider('destinosRechazados')]
    public function test_rechaza_los_destinos_peligrosos(string $destino): void
    {
        $this->assertFalse($this->destinoSeguro($destino), "Deberia rechazarse el destino: {$destino}");
    }

    public static function destinosAdmitidos(): array
    {
        return [
            'raiz del portal'          => ['/'],
            'ruta interna'             => ['/nosotros'],
            'ruta interna profunda'    => ['/transparencia/2026'],
            'ruta con parametros'      => ['/noticias?categoria=institucional'],
            'https externo'            => ['https://www.gob.ec'],
            'https en mayusculas'      => ['HTTPS://WWW.GOB.EC'],
        ];
    }

    public static function destinosRechazados(): array
    {
        return [
            'protocolo relativo'       => ['//sitio-falso.com'],
            'javascript'               => ['javascript:alert(1)'],
            'data uri'                 => ['data:text/html;base64,PHNjcmlwdD4='],
            'http sin cifrar'          => ['http://sitio-falso.com'],
            'ftp'                      => ['ftp://sitio-falso.com'],
            'dominio suelto'           => ['sitio-falso.com'],
        ];
    }
}
