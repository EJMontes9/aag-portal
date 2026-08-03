<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validación de correo endurecida frente a inyección de cabeceras (CRLF).
 *
 * ── Por qué existe esta regla ────────────────────────────────────────────────
 * La regla 'email' que trae Laravel 11 arrastra un aviso de seguridad:
 * "CRLF injection in default email rule". Da por válidas direcciones que
 * contienen saltos de línea, y ese salto de línea, cuando la dirección se usa
 * más tarde como destinatario de un correo, permite cerrar la cabecera "To:" y
 * escribir cabeceras nuevas (Bcc:, Reply-To:, Content-Type:). En la práctica
 * convierte el formulario del portal en un reenviador de spam a nombre de la
 * AAG, y ahí lo que se quema es el dominio institucional.
 *
 * El parche oficial solo existe en Laravel 12.60+ / 13.x. Subir de versión
 * mayor es una decisión del cliente, no algo que se meta de tapadillo antes de
 * publicar, así que se cierra el agujero en la capa de la aplicación: esta
 * regla se aplica ADEMÁS de 'email:rfc', nunca en su lugar.
 *
 * Los dos correos del portal (boletín y formularios de contacto) acaban siendo
 * destinatario real de un envío, que es exactamente el escenario del aviso.
 *
 * Al subir a Laravel 12.60+ esta regla puede quedarse: no estorba y el coste es
 * una comprobación de cadena.
 *
 * @see docs/SEGURIDAD.md - "Avisos aceptados y su mitigación"
 */
class CorreoSeguro implements ValidationRule
{
    /**
     * Longitud máxima real de una dirección de correo (RFC 5321: 254).
     * Se comprueba aquí también porque un 'max:255' en caracteres no equivale
     * a 254 octetos cuando hay multibyte de por medio.
     */
    private const LONGITUD_MAXIMA = 254;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('El correo no es válido.');
            return;
        }

        // Cualquier carácter de control queda fuera: CR y LF son el vector del
        // aviso, pero NUL y el resto del rango C0/C1 tampoco tienen nada que
        // hacer en una dirección y algunos MTA los tratan de forma creativa.
        if (preg_match('/[\x00-\x1F\x7F]/', $value)) {
            $fail('El correo no es válido.');
            return;
        }

        // Una dirección legítima no lleva espacios fuera de comillas. El formato
        // entre comillas del RFC 5321 ("a b"@ejemplo.com) es válido sobre el
        // papel, no lo acepta medio internet, y es un clásico para colar cosas
        // raras: aquí no se admite.
        if (preg_match('/\s/u', $value)) {
            $fail('El correo no es válido.');
            return;
        }

        if (strlen($value) > self::LONGITUD_MAXIMA) {
            $fail('El correo es demasiado largo.');
            return;
        }

        // Exactamente una arroba, y con contenido a ambos lados.
        if (substr_count($value, '@') !== 1) {
            $fail('El correo no es válido.');
            return;
        }

        [$local, $dominio] = explode('@', $value, 2);

        if ($local === '' || $dominio === '') {
            $fail('El correo no es válido.');
            return;
        }

        // Un guion al principio de la dirección es el aviso de symfony/mailer
        // (CVE-2026-45068): el transporte sendmail puede llegar a interpretar
        // ese "-algo" como una opción del binario en vez de como destinatario.
        // El portal usa SMTP, pero esta comprobación cuesta una línea y
        // sobrevive a que alguien cambie el transporte en el cPanel.
        if (str_starts_with($value, '-')) {
            $fail('El correo no es válido.');
            return;
        }

        // El dominio no admite puntos consecutivos ni al principio/final.
        if (str_contains($dominio, '..') || str_starts_with($dominio, '.') || str_ends_with($dominio, '.')) {
            $fail('El correo no es válido.');
            return;
        }
    }
}
