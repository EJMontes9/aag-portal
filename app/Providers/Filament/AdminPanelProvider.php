<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\PortalStatsOverview;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentConvocatoriasWidget;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // ──────────────────────────────────────────────────────────────────────
        // SEGURIDAD -- Cerrar por defecto.
        //
        // Filament, cuando NO encuentra una Policy para el modelo de un
        // Resource, devuelve Response::allow(): permite. El proyecto tiene 15
        // recursos y una sola policy, de modo que todos los demas quedaban
        // abiertos a cualquiera capaz de entrar al panel, incluido el rol
        // "editor": contenido completo del portal, datos personales de
        // suscriptores y envios de formularios de contacto.
        //
        // Con esto, la ausencia de policy DENIEGA en lugar de permitir. Es la
        // configuracion segura, y ademas hace que falte cualquier permiso se
        // note de inmediato en vez de pasar inadvertido.
        //
        // Requiere que existan las policies: se generan con
        //   php artisan shield:generate --all
        // y se asignan al super_admin con
        //   php artisan shield:super-admin --user=1
        // ──────────────────────────────────────────────────────────────────────
        Resource::checkPolicyExistence(false);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // Pagina de perfil: sin ella no habia NINGUNA forma de cambiar la
            // contrasena desde la aplicacion, lo que obligaba a dejar la que
            // sembraba el seeder.
            ->profile()
            ->passwordReset()
            ->brandName('Autoridad Aeroportuaria de Guayaquil')
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('favicon.ico'))
            ->defaultThemeMode(ThemeMode::Light)
            ->colors([
                'primary' => [
                    50  => '240, 245, 252',
                    100 => '219, 230, 247',
                    200 => '184, 206, 238',
                    300 => '143, 178, 226',
                    400 => '95, 143, 211',
                    500 => '46, 95, 169',
                    600 => '30, 58, 138',
                    700 => '24, 47, 112',
                    800 => '18, 36, 86',
                    900 => '14, 28, 67',
                    950 => '11, 21, 51',
                ],
                'gray' => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'info' => Color::Sky,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->navigationGroups([
                NavigationGroup::make()->label('Configuracion')->icon('heroicon-o-cog-6-tooth')->collapsed(false),
                NavigationGroup::make()->label('Contenido')->icon('heroicon-o-document-text')->collapsed(false),
                NavigationGroup::make()->label('Transparencia')->icon('heroicon-o-archive-box')->collapsed(false),
                NavigationGroup::make()->label('Usuarios y Roles')->icon('heroicon-o-shield-check')->collapsed(true),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                QuickActionsWidget::class,
                PortalStatsOverview::class,
                RecentConvocatoriasWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
