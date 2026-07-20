<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
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

        $this->configurarAutorizacion();
    }

    /**
     * SEGURIDAD -- Cierre por defecto de la autorizacion.
     *
     * Filament, cuando no encuentra una Policy para un modelo, DEVUELVE
     * Response::allow(): es decir, permite. El proyecto tiene 15 recursos y
     * una sola policy (RolePolicy), asi que todos los demas quedaban abiertos
     * a cualquier usuario capaz de entrar al panel -- incluido el rol
     * "editor". Eso incluye los datos personales de suscriptores y los envios
     * de formularios de contacto.
     *
     * Filament Shield esta instalado pero nunca se activo: no hay permisos en
     * la base de datos ni configuracion publicada, asi que no compensaba nada.
     *
     * Este Gate::before invierte el criterio: quien no sea super_admin solo
     * puede hacer aquello para lo que exista un permiso explicito. Devolver
     * null (en vez de false) deja que sigan evaluandose las policies y los
     * permisos de Spatie; lo que se corta es el "permitir por ausencia".
     */
    protected function configurarAutorizacion(): void
    {
        Gate::before(function ($user, string $ability) {
            // El super_admin conserva acceso total. Es lo que hace Shield
            // cuando esta bien configurado, y evita quedarse fuera del panel.
            if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
                return true;
            }

            return null; // sigue el flujo normal de policies y permisos
        });
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
