<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
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

        $this->configurarProxies();
    }

    /**
     * Declara en qué proxies inversos se confía (Cloudflare, normalmente).
     *
     * ── Por qué aquí y no en bootstrap/app.php ──────────────────────────────
     * Lo natural sería ponerlo en el closure withMiddleware() de
     * bootstrap/app.php, pero ese closure corre mientras se CONSTRUYE la
     * aplicación: el contenedor aún no tiene registrado el servicio 'config',
     * y una llamada a config() allí lanza
     *
     *     BindingResolutionException: Target class [config] does not exist
     *
     * que no deja arrancar NADA: el sitio entero responde 500. Aquí, en boot(),
     * la configuración ya está disponible.
     *
     * ── Qué resuelve ────────────────────────────────────────────────────────
     * Detrás de un proxy, la IP que ve Apache es la del proxy y no la del
     * visitante. Sin esto, todos los visitantes cuentan como uno solo: el
     * límite del boletín (5 intentos por IP) se vuelve global y cinco intentos
     * de cualquiera bloquean el formulario para todo el mundo. Además Laravel
     * no detecta el HTTPS original y puede generar enlaces http://.
     *
     * Por defecto no se confía en nadie: confiar de más permite falsear la IP
     * con una cabecera X-Forwarded-For inventada. Ver config/proxies.php.
     */
    protected function configurarProxies(): void
    {
        $proxies = config('proxies.trusted');

        if ($proxies === 'cloudflare') {
            $proxies = config('proxies.cloudflare');
        } elseif (is_string($proxies) && $proxies !== '' && $proxies !== '*') {
            $proxies = array_filter(array_map('trim', explode(',', $proxies)));
        }

        if (empty($proxies)) {
            return;
        }

        TrustProxies::at($proxies);
        TrustProxies::withHeaders(
            Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
        );
    }

    /**
     * SEGURIDAD -- Cierre por defecto de la autorización.
     *
     * Filament, cuando no encuentra una Policy para un modelo, DEVUELVE
     * Response::allow(): es decir, permite. El proyecto tiene 15 recursos y
     * una sola policy (RolePolicy), así que todos los demás quedaban abiertos
     * a cualquier usuario capaz de entrar al panel -- incluido el rol
     * "editor". Eso incluye los datos personales de suscriptores y los envíos
     * de formularios de contacto.
     *
     * Filament Shield está instalado pero nunca se activó: no hay permisos en
     * la base de datos ni configuración publicada, así que no compensaba nada.
     *
     * Este Gate::before invierte el criterio: quien no sea super_admin solo
     * puede hacer aquello para lo que exista un permiso explícito. Devolver
     * null (en vez de false) deja que sigan evaluándose las policies y los
     * permisos de Spatie; lo que se corta es el "permitir por ausencia".
     */
    protected function configurarAutorizacion(): void
    {
        Gate::before(function ($user, string $ability, array $arguments = []) {
            $objetivo = $arguments[0] ?? null;

            // EXCEPCIÓN antes que nada: nadie borra su propia cuenta, ni
            // siquiera un super_admin.
            //
            // Esta comprobación tiene que ir AQUÍ y no solo en UserPolicy:
            // el "return true" de abajo cortocircuita las policies, de modo
            // que para un super_admin la de usuarios no llegaba a evaluarse
            // y podía eliminarse a sí mismo, dejando el portal potencialmente
            // sin ninguna cuenta con acceso total.
            if ($objetivo instanceof \App\Models\User
                && $objetivo->id === $user->id
                && in_array($ability, ['delete', 'forceDelete'], true)) {
                return false;
            }

            // El super_admin conserva acceso total. Es lo que hace Shield
            // cuando está bien configurado, y evita quedarse fuera del panel.
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
