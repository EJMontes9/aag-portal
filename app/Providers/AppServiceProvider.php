<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ──────────────────────────────────────────────────────────────────────
        // Configuración dinámica de correo desde la tabla site_settings.
        // Sobreescribe los valores del .env cuando hay configuración guardada en BD.
        // ──────────────────────────────────────────────────────────────────────
        $this->applyMailSettings();
    }

    protected function applyMailSettings(): void
    {
        try {
            // Solo aplica si hay un driver SMTP configurado en la BD
            $mailer = SiteSetting::get('mail_mailer');
            $host   = SiteSetting::get('mail_host');

            if (! $mailer || ! $host) {
                return; // Usa la configuración del .env
            }

            // Descifrar contraseña almacenada
            $encryptedPwd = SiteSetting::get('mail_password', '');
            $password = '';
            if ($encryptedPwd) {
                try {
                    $password = Crypt::decryptString($encryptedPwd);
                } catch (\Throwable) {
                    $password = $encryptedPwd; // compatibilidad: si no está cifrada
                }
            }

            config([
                'mail.default'                    => $mailer,
                'mail.mailers.smtp.host'          => $host,
                'mail.mailers.smtp.port'          => (int) SiteSetting::get('mail_port', 587),
                'mail.mailers.smtp.encryption'    => SiteSetting::get('mail_encryption', 'tls') ?: null,
                'mail.mailers.smtp.username'      => SiteSetting::get('mail_username', ''),
                'mail.mailers.smtp.password'      => $password,
                'mail.from.address'               => SiteSetting::get('mail_from_address', config('mail.from.address')),
                'mail.from.name'                  => SiteSetting::get('mail_from_name', config('app.name')),
            ]);
        } catch (\Throwable) {
            // Si la tabla aún no existe (fresh install) o falla, ignora y usa .env
        }
    }
}
