<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validacion de correo endurecida frente a inyeccion de cabeceras (CRLF).
 *
 * ── Por que existe esta regla ────────────────────────────────────────────────
 * La regla 'email' que trae Laravel 11 arrastra un aviso de seguridad:
 * "CRLF injection in default email rule". Da por validas direcciones que
 * contienen saltos de linea, y ese salto de linea, cuando la direccion se usa
 * mas tarde como destinatario de un correo, permite cerrar la cabecera "To:" y
 * escribir cabeceras nuevas (Bcc:, Reply-To:, Content-Type:). En la practica
 * convierte el formulario del portal en un reenviador de spam a nombre de la
 * AAG, y ahi lo que se quema es el dominio institucional.
 *
 * El parche oficial solo existe en Laravel 12.60+ / 13.x. Subir de version
 * mayor es una decision del cliente, no algo que se meta de tapadillo antes de
 * publicar, asi que se cierra el agujero en la capa de la aplicacion: esta
 * regla se aplica ADEMAS de 'email:rfc', nunca en su lugar.
 *
 * Los dos correos del portal (boletin y formularios de contacto) acaban siendo
 * destinatario real de un envio, que es exactamente el escenario del aviso.
 *
 * Al subir a Laravel 12.60+ esta regla puede quedarse: no estorba y el coste es
 * una comprobacion de cadena.
 *
 * @see docs/SEGURIDAD.md - "Avisos aceptados y su mitigacion"
 */
class CorreoSeguro implements ValidationRule
{
    /**
     * Longitud maxima real de una direccion de correo (RFC 5321: 254).
     * Se comprueba aqui tambien porque un 'max:255' en caracteres no equivale
     * a 254 octetos cuando hay multibyte de por medio.
     */
    private const LONGITUD_MAXIMA = 254;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('El correo no es valido.');
            return;
        }

        // Cualquier caracter de control queda fuera: CR y LF son el vector del
        // aviso, pero NUL y el resto del rango C0/C1 tampoco tienen nada que
        // hacer en una direccion y algunos MTA los tratan de forma creativa.
        if (preg_match('/[\x00-\x1F\x7F]/', $value)) {
            $fail('El correo no es valido.');
            return;
        }

        // Una direccion legitima no lleva espacios fuera de comillas. El formato
        // entre comillas del RFC 5321 ("a b"@ejemplo.com) es valido sobre el
        // papel, no lo acepta medio internet, y es un clasico para colar cosas
        // raras: aqui no se admite.
        if (preg_match('/\s/u', $value)) {
            $fail('El correo no es valido.');
            return;
        }

        if (strlen($value) > self::LONGITUD_MAXIMA) {
            $fail('El correo es demasiado largo.');
            return;
        }

        // Exactamente una arroba, y con contenido a ambos lados.
        if (substr_count($value, '@') !== 1) {
            $fail('El correo no es valido.');
            return;
        }

        [$local, $dominio] = explode('@', $value, 2);

        if ($local === '' || $dominio === '') {
            $fail('El correo no es valido.');
            return;
        }

        // Un guion al principio de la direccion es el aviso de symfony/mailer
        // (CVE-2026-45068): el transporte sendmail puede llegar a interpretar
        // ese "-algo" como una opcion del binario en vez de como destinatario.
        // El portal usa SMTP, pero esta comprobacion cuesta una linea y
        // sobrevive a que alguien cambie el transporte en el cPanel.
        if (str_starts_with($value, '-')) {
            $fail('El correo no es valido.');
            return;
        }

        // El dominio no admite puntos consecutivos ni al principio/final.
        if (str_contains($dominio, '..') || str_starts_with($dominio, '.') || str_ends_with($dominio, '.')) {
            $fail('El correo no es valido.');
            return;
        }
    }
}
