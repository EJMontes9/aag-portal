<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $roles = ['super_admin', 'admin', 'editor', 'publisher'];
        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName);
        }

        $this->crearUsuariosIniciales();

        $settings = [
            // Identidad
            ['key' => 'site_name', 'group' => 'general', 'value' => 'Autoridad Aeroportuaria de Guayaquil', 'type' => 'string'],
            ['key' => 'site_slogan', 'group' => 'general', 'value' => 'Conectar Guayaquil con el mundo, con claridad', 'type' => 'string'],
            ['key' => 'site_description', 'group' => 'general', 'value' => 'Portal oficial de la Autoridad Aeroportuaria de Guayaquil', 'type' => 'string'],

            // Tipografias -- ambas auto-hospedadas en /public/fonts (ver @font-face
            // en resources/css/app.css). Barlow Condensed es CONDENSADA: si se
            // cambia por una que no lo sea, se rompe el ritmo horizontal de la
            // Propuesta B.
            ['key' => 'font_serif', 'group' => 'typography', 'value' => 'Neulis Black', 'type' => 'string'],
            ['key' => 'font_sans', 'group' => 'typography', 'value' => 'Barlow Condensed', 'type' => 'string'],
            ['key' => 'font_mono', 'group' => 'typography', 'value' => 'JetBrains Mono', 'type' => 'string'],

            // Paleta AAG -- Propuesta B (manual de identidad institucional)
            ['key' => 'color_navy', 'group' => 'colors', 'value' => '#2E2F63', 'type' => 'string'],
            ['key' => 'color_primary', 'group' => 'colors', 'value' => '#009CDF', 'type' => 'string'],
            ['key' => 'color_accent', 'group' => 'colors', 'value' => '#EFC600', 'type' => 'string'],
            ['key' => 'color_soft', 'group' => 'colors', 'value' => '#E5F4FB', 'type' => 'string'],
            ['key' => 'color_bg_light', 'group' => 'colors', 'value' => '#F5F5F5', 'type' => 'string'],
            ['key' => 'color_fg_light', 'group' => 'colors', 'value' => '#222222', 'type' => 'string'],
            ['key' => 'color_bg_dark', 'group' => 'colors', 'value' => '#0B0F1E', 'type' => 'string'],
            ['key' => 'color_fg_dark', 'group' => 'colors', 'value' => '#E2E8F0', 'type' => 'string'],

            // Tema
            ['key' => 'dark_mode_enabled', 'group' => 'theme', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'default_theme', 'group' => 'theme', 'value' => 'light', 'type' => 'string'],

            // Animaciones
            ['key' => 'animations_enabled', 'group' => 'animations', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'animations_speed', 'group' => 'animations', 'value' => 'normal', 'type' => 'string'],
            ['key' => 'animations_on_mobile', 'group' => 'animations', 'value' => '1', 'type' => 'boolean'],

            // Contacto
            ['key' => 'contact_address', 'group' => 'contact', 'value' => 'Aeropuerto Jose Joaquin de Olmedo, Edificio Administrativo, 1er Piso, Guayaquil, Ecuador', 'type' => 'string'],
            ['key' => 'contact_phone', 'group' => 'contact', 'value' => '(593) 4 2169209', 'type' => 'string'],
            ['key' => 'contact_email', 'group' => 'contact', 'value' => 'info@aag.gob.ec', 'type' => 'string'],

            // Header
            ['key' => 'header_cta_enabled', 'group' => 'header', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'header_cta_label', 'group' => 'header', 'value' => 'Estado de vuelos', 'type' => 'string'],
            ['key' => 'header_cta_url', 'group' => 'header', 'value' => '#vuelos', 'type' => 'string'],
            ['key' => 'header_show_clock', 'group' => 'header', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'topbar_enabled', 'group' => 'header', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'topbar_text', 'group' => 'header', 'value' => 'Aeropuerto Internacional Jose Joaquin de Olmedo · Guayaquil, Ecuador', 'type' => 'string'],
            ['key' => 'topbar_faq_label', 'group' => 'header', 'value' => 'PREGUNTAS FRECUENTES', 'type' => 'string'],

            // Footer
            ['key' => 'footer_about', 'group' => 'footer', 'value' => 'Fundacion de la Muy Ilustre Municipalidad de Guayaquil. Administramos y supervisamos el Aeropuerto Internacional Jose Joaquin de Olmedo.', 'type' => 'string'],
            ['key' => 'footer_copyright', 'group' => 'footer', 'value' => '© ' . date('Y') . ' Autoridad Aeroportuaria de Guayaquil. Todos los derechos reservados.', 'type' => 'string'],

            // SEO
            ['key' => 'seo_meta_title', 'group' => 'seo', 'value' => 'AAG - Autoridad Aeroportuaria de Guayaquil', 'type' => 'string'],
            ['key' => 'seo_meta_description', 'group' => 'seo', 'value' => 'Portal oficial de la Autoridad Aeroportuaria de Guayaquil, encargada del aeropuerto Jose Joaquin de Olmedo.', 'type' => 'string'],

            // Redes
            ['key' => 'social_facebook', 'group' => 'social', 'value' => 'https://www.facebook.com/AAGEcuador', 'type' => 'string'],
            ['key' => 'social_twitter', 'group' => 'social', 'value' => '', 'type' => 'string'],
            ['key' => 'social_instagram', 'group' => 'social', 'value' => '', 'type' => 'string'],
            ['key' => 'social_youtube', 'group' => 'social', 'value' => '', 'type' => 'string'],
            ['key' => 'social_linkedin', 'group' => 'social', 'value' => '', 'type' => 'string'],
        ];

        foreach ($settings as $s) {
            SiteSetting::updateOrCreate(['key' => $s['key']], $s);
        }

        $headerMenu = Menu::updateOrCreate(
            ['slug' => 'header-principal'],
            ['name' => 'Menu Principal', 'location' => 'header', 'is_active' => true]
        );

        MenuItem::where('menu_id', $headerMenu->id)->delete();

        $headerItems = [
            ['label' => 'Nosotros', 'url' => '/nosotros', 'sort_order' => 1],
            ['label' => 'Sala de prensa', 'url' => '/noticias', 'sort_order' => 2],
            ['label' => 'Transparencia', 'url' => '/transparencia', 'sort_order' => 3],
            ['label' => 'Guia de viaje', 'url' => '/guia-viaje', 'sort_order' => 4],
            ['label' => 'Trabaja con nosotros', 'url' => '/trabaja-con-nosotros', 'sort_order' => 5],
        ];

        foreach ($headerItems as $item) {
            MenuItem::create(array_merge($item, ['menu_id' => $headerMenu->id, 'is_active' => true]));
        }

        $footerMenu = Menu::updateOrCreate(
            ['slug' => 'footer-enlaces'],
            ['name' => 'Enlaces', 'location' => 'footer', 'is_active' => true]
        );

        $footerItems = [
            ['label' => 'Nosotros', 'url' => '/nosotros', 'sort_order' => 1],
            ['label' => 'Sala de prensa', 'url' => '/noticias', 'sort_order' => 2],
            ['label' => 'Transparencia', 'url' => '/transparencia', 'sort_order' => 3],
            ['label' => 'Guia de viaje', 'url' => '/guia-viaje', 'sort_order' => 4],
            ['label' => 'Trabaja con nosotros', 'url' => '/trabaja-con-nosotros', 'sort_order' => 5],
        ];

        $institutionsMenu = Menu::updateOrCreate(
            ['slug' => 'footer-instituciones'],
            ['name' => 'Instituciones', 'location' => 'footer_secondary', 'is_active' => true]
        );

        $institutionItems = [
            ['label' => 'Municipio de Guayaquil', 'url' => 'https://www.guayaquil.gob.ec', 'target' => '_blank', 'sort_order' => 1],
            ['label' => 'Registro Civil', 'url' => 'https://www.registrocivil.gob.ec', 'target' => '_blank', 'sort_order' => 2],
            ['label' => 'Corporacion Seguridad Ciudadana', 'url' => '#', 'sort_order' => 3],
            ['label' => 'Autoridad de Transito', 'url' => '#', 'sort_order' => 4],
            ['label' => 'Terminal Terrestre', 'url' => '#', 'sort_order' => 5],
        ];

        foreach ($institutionItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $institutionsMenu->id, 'label' => $item['label']],
                array_merge($item, ['menu_id' => $institutionsMenu->id, 'is_active' => true])
            );
        }

        foreach ($footerItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $footerMenu->id, 'label' => $item['label']],
                array_merge($item, ['menu_id' => $footerMenu->id, 'is_active' => true])
            );
        }

        $this->call(HomeContentSeeder::class);
        $this->call(NewsSeeder::class);
        $this->call(InstitutionalPagesSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(ProjectSeeder::class);
        $this->call(TransparencySeeder::class);
    }

    /**
     * Crea las cuentas iniciales del panel.
     *
     * SEGURIDAD -- Antes esto sembraba admin@aag.gob.ec y editor@aag.gob.ec
     * con la contrasena literal "password", y ademas usaba updateOrCreate, de
     * modo que CADA ejecucion del seeder volvia a ponerla, revirtiendo
     * cualquier cambio que se hubiera hecho a mano. Como la aplicacion no
     * tenia pagina de perfil ni recuperacion por correo, esa contrasena no
     * habia forma de cambiarla desde dentro. Era la puerta de entrada al
     * panel para cualquiera que conociera el patron.
     *
     * Ahora:
     *   - Nunca se toca la contrasena de un usuario que ya existe.
     *   - Si hay que crearlo, la contrasena es aleatoria y se imprime UNA sola
     *     vez por consola, para copiarla y cambiarla en el primer acceso.
     *   - Se puede fijar una concreta con SEED_ADMIN_PASSWORD en el entorno,
     *     util para despliegues automatizados.
     */
    protected function crearUsuariosIniciales(): void
    {
        $cuentas = [
            ['email' => 'admin@aag.gob.ec',  'name' => 'Administrador AAG', 'rol' => 'super_admin'],
            ['email' => 'editor@aag.gob.ec', 'name' => 'Editor AAG',        'rol' => 'editor'],
        ];

        foreach ($cuentas as $cuenta) {
            $existente = User::where('email', $cuenta['email'])->first();

            if ($existente) {
                // Se respetan tanto la contrasena como los roles ya asignados.
                $this->command?->info("Usuario {$cuenta['email']} ya existe: no se modifica.");
                continue;
            }

            $plano = env('SEED_ADMIN_PASSWORD') ?: Str::password(20);

            $usuario = User::create([
                'name'     => $cuenta['name'],
                'email'    => $cuenta['email'],
                'password' => Hash::make($plano),
            ]);
            $usuario->syncRoles([$cuenta['rol']]);

            $this->command?->warn("Usuario creado: {$cuenta['email']}");
            $this->command?->warn("  Contrasena: {$plano}");
            $this->command?->warn('  Guardala ahora y cambiala tras el primer acceso: no se volvera a mostrar.');
        }
    }
}
