<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class SiteSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Configuracion';
    protected static ?string $navigationLabel = 'Configuracion del Sitio';
    protected static ?string $title = 'Configuracion del Sitio';
    protected static string $view = 'filament.pages.site-settings-page';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::allCached());
    }

    public function form(Form $form): Form
    {
        $serifFonts = [
            'Fraunces' => 'Fraunces (recomendada - serif instrumental moderna)',
            'Playfair Display' => 'Playfair Display',
            'Lora' => 'Lora',
            'EB Garamond' => 'EB Garamond',
            'Cormorant Garamond' => 'Cormorant Garamond',
        ];

        $sansFonts = [
            'Inter' => 'Inter (recomendada - UI optimizada)',
            'DM Sans' => 'DM Sans',
            'Manrope' => 'Manrope',
            'Plus Jakarta Sans' => 'Plus Jakarta Sans',
            'Figtree' => 'Figtree',
        ];

        $monoFonts = [
            'JetBrains Mono' => 'JetBrains Mono (recomendada - tablero aeroportuario)',
            'IBM Plex Mono' => 'IBM Plex Mono',
            'Fira Code' => 'Fira Code',
        ];

        return $form
            ->schema([
                Tabs::make('SiteSettings')
                    ->tabs([
                        Tab::make('Identidad')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Section::make('Informacion Institucional')
                                    ->schema([
                                        TextInput::make('site_name')->label('Nombre del sitio')->required()->default('Autoridad Aeroportuaria de Guayaquil'),
                                        TextInput::make('site_slogan')->label('Slogan / Tagline'),
                                        Textarea::make('site_description')->label('Descripcion corta')->rows(2),
                                        FileUpload::make('site_logo')->label('Logo principal')->image()->directory('branding')->disk('public')->imageEditor(),
                                        FileUpload::make('site_logo_dark')->label('Logo para modo oscuro (opcional)')->image()->directory('branding')->disk('public')->imageEditor(),
                                        FileUpload::make('site_logo_footer')->label('Logo footer (opcional)')->image()->directory('branding')->disk('public'),
                                        FileUpload::make('site_favicon')->label('Favicon (.ico, .png)')->directory('branding')->disk('public'),
                                    ])->columns(2),
                            ]),
                        Tab::make('Tipografias')
                            ->icon('heroicon-o-language')
                            ->schema([
                                Section::make('Familias tipograficas')
                                    ->description('Elige la combinacion de 3 familias: titular (serif), cuerpo/UI (sans) y datos (monospace). Todas cargan desde Google Fonts.')
                                    ->schema([
                                        Select::make('font_serif')->label('Titulares (serif)')->options($serifFonts)->default('Fraunces')->selectablePlaceholder(false)->required(),
                                        Select::make('font_sans')->label('UI y cuerpo (sans)')->options($sansFonts)->default('Inter')->selectablePlaceholder(false)->required(),
                                        Select::make('font_mono')->label('Datos y cifras (mono)')->options($monoFonts)->default('JetBrains Mono')->selectablePlaceholder(false)->required(),
                                    ])->columns(3),
                            ]),
                        Tab::make('Colores')
                            ->icon('heroicon-o-swatch')
                            ->schema([
                                Section::make('Paleta institucional AAG')
                                    ->description('Tokens principales usados en todo el portal. Aceptan formato hex (#0B1E4A).')
                                    ->schema([
                                        ColorPicker::make('color_navy')->label('Navy institucional')->default('#0B1E4A'),
                                        ColorPicker::make('color_primary')->label('Azul accion primaria')->default('#1E3A8A'),
                                        ColorPicker::make('color_accent')->label('Azul acento')->default('#5B8FD9'),
                                        ColorPicker::make('color_soft')->label('Azul fondo suave')->default('#CFE0F3'),
                                    ])->columns(4),
                                Section::make('Colores base (opcional - solo tocar si es necesario)')
                                    ->collapsed()
                                    ->schema([
                                        ColorPicker::make('color_bg_light')->label('Fondo (tema claro)')->default('#FAFAFB'),
                                        ColorPicker::make('color_fg_light')->label('Texto (tema claro)')->default('#0F172A'),
                                        ColorPicker::make('color_bg_dark')->label('Fondo (tema oscuro)')->default('#0B0F1E'),
                                        ColorPicker::make('color_fg_dark')->label('Texto (tema oscuro)')->default('#E2E8F0'),
                                    ])->columns(4),
                            ]),
                        Tab::make('Tema')
                            ->icon('heroicon-o-sun')
                            ->schema([
                                Section::make('Modo claro / oscuro')
                                    ->description('Controla si los visitantes pueden cambiar entre modo claro y oscuro.')
                                    ->schema([
                                        Toggle::make('dark_mode_enabled')
                                            ->label('Permitir al visitante cambiar a modo oscuro')
                                            ->helperText('Si esta desactivado, el sitio siempre se muestra en el tema por defecto.')
                                            ->default(true),
                                        Select::make('default_theme')
                                            ->label('Tema por defecto')
                                            ->options([
                                                'light' => 'Claro',
                                                'dark' => 'Oscuro',
                                                'system' => 'Seguir la preferencia del sistema del visitante',
                                            ])
                                            ->default('light')
                                            ->required(),
                                    ])->columns(2),
                            ]),
                        Tab::make('Contacto')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Section::make('Datos de Contacto')
                                    ->schema([
                                        Textarea::make('contact_address')->label('Direccion')->rows(2),
                                        TextInput::make('contact_phone')->label('Telefono'),
                                        TextInput::make('contact_email')->label('Email')->email(),
                                        Textarea::make('contact_map_embed')->label('HTML embed del mapa (Google Maps iframe)')->rows(3),
                                    ])->columns(2),
                            ]),
                        Tab::make('Redes Sociales')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Section::make('Enlaces a Redes Sociales')
                                    ->schema([
                                        TextInput::make('social_facebook')->label('Facebook')->url(),
                                        TextInput::make('social_twitter')->label('Twitter / X')->url(),
                                        TextInput::make('social_instagram')->label('Instagram')->url(),
                                        TextInput::make('social_youtube')->label('YouTube')->url(),
                                        TextInput::make('social_linkedin')->label('LinkedIn')->url(),
                                        TextInput::make('social_tiktok')->label('TikTok')->url(),
                                    ])->columns(2),
                            ]),
                        Tab::make('Header / CTA')
                            ->icon('heroicon-o-bars-3-center-left')
                            ->schema([
                                Section::make('Boton CTA del header')
                                    ->description('Boton destacado en el header (ej: "Estado de vuelos").')
                                    ->schema([
                                        Toggle::make('header_cta_enabled')->label('Mostrar boton CTA')->default(true),
                                        TextInput::make('header_cta_label')->label('Etiqueta del boton')->default('Estado de vuelos'),
                                        TextInput::make('header_cta_url')->label('URL destino')->default('#'),
                                        Toggle::make('header_show_clock')->label('Mostrar reloj Guayaquil (GYE)')->default(true),
                                    ])->columns(2),
                                Section::make('Franja superior (topbar)')
                                    ->schema([
                                        Toggle::make('topbar_enabled')->label('Mostrar franja superior')->default(true),
                                        TextInput::make('topbar_text')->label('Texto institucional')->default('Aeropuerto Internacional Jose Joaquin de Olmedo · Guayaquil, Ecuador'),
                                        TextInput::make('topbar_faq_label')->label('Etiqueta FAQ')->default('PREGUNTAS FRECUENTES'),
                                    ])->columns(2),
                            ]),
                        Tab::make('Animaciones')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Section::make('Animaciones del sitio publico')
                                    ->description('Controla el movimiento del frontend. Las animaciones siempre se desactivan automaticamente si el usuario tiene "reducir movimiento" activado en su sistema operativo.')
                                    ->schema([
                                        Toggle::make('animations_enabled')
                                            ->label('Animaciones activas')
                                            ->helperText('Si esta desactivado, todo el sitio se muestra estatico.')
                                            ->default(true),
                                        Select::make('animations_speed')
                                            ->label('Velocidad de animaciones')
                                            ->options([
                                                'slow' => 'Lenta (mas suave)',
                                                'normal' => 'Normal (recomendada)',
                                                'fast' => 'Rapida (mas dinamica)',
                                            ])
                                            ->default('normal')
                                            ->required(),
                                        Toggle::make('animations_on_mobile')
                                            ->label('Animaciones en moviles')
                                            ->helperText('Desactivar en moviles ahorra bateria y mejora rendimiento en dispositivos antiguos.')
                                            ->default(true),
                                    ])->columns(3),
                            ]),
                        Tab::make('Correo SMTP')
                            ->icon('heroicon-o-envelope-open')
                            ->schema([
                                Section::make('Servidor de correo saliente')
                                    ->description('Configura cómo el portal envía notificaciones (formularios, alertas). Los datos se guardan cifrados.')
                                    ->schema([

                                        // ── Preset selector ──────────────────────────────────
                                        Select::make('mail_preset')
                                            ->label('Proveedor (autocompletar)')
                                            ->options([
                                                'gmail'   => '📧 Gmail',
                                                'outlook' => '📧 Outlook / Hotmail / Office 365',
                                                'yahoo'   => '📧 Yahoo Mail',
                                                'ses'     => '☁️ Amazon SES',
                                                'custom'  => '⚙️ Servidor personalizado',
                                            ])
                                            ->placeholder('Selecciona para autocompletar...')
                                            ->live()
                                            ->afterStateUpdated(function (?string $state, Set $set) {
                                                match ($state) {
                                                    'gmail' => [
                                                        $set('mail_mailer',     'smtp'),
                                                        $set('mail_host',       'smtp.gmail.com'),
                                                        $set('mail_port',       '587'),
                                                        $set('mail_encryption', 'tls'),
                                                    ],
                                                    'outlook' => [
                                                        $set('mail_mailer',     'smtp'),
                                                        $set('mail_host',       'smtp-mail.outlook.com'),
                                                        $set('mail_port',       '587'),
                                                        $set('mail_encryption', 'tls'),
                                                    ],
                                                    'yahoo' => [
                                                        $set('mail_mailer',     'smtp'),
                                                        $set('mail_host',       'smtp.mail.yahoo.com'),
                                                        $set('mail_port',       '465'),
                                                        $set('mail_encryption', 'ssl'),
                                                    ],
                                                    'ses' => [
                                                        $set('mail_mailer',     'smtp'),
                                                        $set('mail_host',       'email-smtp.us-east-1.amazonaws.com'),
                                                        $set('mail_port',       '587'),
                                                        $set('mail_encryption', 'tls'),
                                                    ],
                                                    default => null,
                                                };
                                            })
                                            ->helperText('Elige tu proveedor para autocompletar host, puerto y cifrado.')
                                            ->columnSpanFull(),

                                        // ── Configuración SMTP ───────────────────────────────
                                        Select::make('mail_mailer')
                                            ->label('Driver')
                                            ->options([
                                                'smtp' => 'SMTP (envío real)',
                                                'log'  => 'Log (solo desarrollo — no envía)',
                                            ])
                                            ->default('smtp')
                                            ->required(),

                                        Select::make('mail_encryption')
                                            ->label('Cifrado')
                                            ->options([
                                                'tls'  => 'TLS (recomendado — puerto 587)',
                                                'ssl'  => 'SSL (puerto 465)',
                                                ''     => 'Sin cifrado (no recomendado)',
                                            ])
                                            ->default('tls'),

                                        TextInput::make('mail_host')
                                            ->label('Servidor SMTP (host)')
                                            ->placeholder('smtp.gmail.com'),

                                        TextInput::make('mail_port')
                                            ->label('Puerto')
                                            ->numeric()
                                            ->default('587')
                                            ->placeholder('587'),

                                        TextInput::make('mail_username')
                                            ->label('Usuario / Email de la cuenta')
                                            ->email()
                                            ->placeholder('tucorreo@gmail.com'),

                                        TextInput::make('mail_password')
                                            ->label('Contraseña')
                                            ->password()
                                            ->revealable()
                                            ->placeholder(
                                                SiteSetting::get('mail_password')
                                                    ? '••••••••  (ya configurada — dejar en blanco para no cambiar)'
                                                    : 'Contraseña o App Password'
                                            )
                                            ->helperText('Para Gmail: necesitas una "Contraseña de aplicación" (Google Account → Seguridad → Contraseñas de aplicación). NO uses tu contraseña normal.'),

                                    ])->columns(2),

                                Section::make('Remitente y prueba')
                                    ->schema([
                                        TextInput::make('mail_from_address')
                                            ->label('Correo remitente (From)')
                                            ->email()
                                            ->placeholder('no-reply@tudominio.com')
                                            ->helperText('En Gmail, debe ser la misma cuenta del usuario SMTP.'),

                                        TextInput::make('mail_from_name')
                                            ->label('Nombre del remitente')
                                            ->placeholder('Portal AAG'),

                                        TextInput::make('mail_test_to')
                                            ->label('Enviar correo de prueba a')
                                            ->email()
                                            ->placeholder('tu@email.com')
                                            ->helperText('Guarda los cambios primero, luego ingresa un email y presiona "Enviar prueba".')
                                            ->suffixAction(
                                                FormAction::make('sendTest')
                                                    ->label('Enviar prueba')
                                                    ->icon('heroicon-o-paper-airplane')
                                                    ->action(function ($get) {
                                                        $to = $get('mail_test_to');
                                                        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
                                                            Notification::make()->title('Ingresa un email válido')->warning()->send();
                                                            return;
                                                        }
                                                        try {
                                                            Mail::raw(
                                                                '✅ Correo de prueba enviado correctamente desde el Portal AAG.' . PHP_EOL . 'Si ves este mensaje, la configuración SMTP funciona.',
                                                                function ($msg) use ($to) {
                                                                    $msg->to($to)->subject('[Portal AAG] Prueba de correo');
                                                                }
                                                            );
                                                            activity()
                                                                ->causedBy(auth()->user())
                                                                ->withProperties(['to' => $to, 'host' => SiteSetting::get('mail_host'), 'mailer' => SiteSetting::get('mail_mailer')])
                                                                ->event('mail_sent')
                                                                ->log("Correo de prueba SMTP enviado a {$to}");
                                                            Notification::make()->title("Correo de prueba enviado a {$to}")->success()->send();
                                                        } catch (\Throwable $e) {
                                                            activity()
                                                                ->causedBy(auth()->user())
                                                                ->withProperties(['to' => $to, 'error' => $e->getMessage()])
                                                                ->event('mail_failed')
                                                                ->log("Error al enviar correo de prueba a {$to}: " . $e->getMessage());
                                                            Notification::make()
                                                                ->title('Error al enviar')
                                                                ->body($e->getMessage())
                                                                ->danger()
                                                                ->send();
                                                        }
                                                    })
                                            ),
                                    ])->columns(2),
                            ]),

                        Tab::make('Footer y SEO')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Footer')
                                    ->schema([
                                        Textarea::make('footer_about')->label('Texto sobre la institucion')->rows(3),
                                        TextInput::make('footer_copyright')->label('Texto copyright')->default('© 2026 Autoridad Aeroportuaria de Guayaquil'),
                                    ]),

                                Section::make('SEO — Meta tags globales')
                                    ->description('Estos valores se usan en páginas que no tienen meta title/description propios.')
                                    ->schema([
                                        TextInput::make('seo_meta_title')
                                            ->label('Meta title por defecto')
                                            ->helperText('Máx. 60 caracteres. Ejemplo: Aeropuerto de Guayaquil | AAG')
                                            ->maxLength(60),
                                        Textarea::make('seo_meta_description')
                                            ->label('Meta description por defecto')
                                            ->helperText('Máx. 160 caracteres. Aparece en los resultados de Google.')
                                            ->rows(2)
                                            ->maxLength(160),
                                        FileUpload::make('seo_og_image')
                                            ->label('Imagen OG por defecto (1200×630 px)')
                                            ->helperText('Se muestra al compartir el sitio en redes sociales. Usa proporción 1.91:1.')
                                            ->image()
                                            ->directory('seo')
                                            ->disk('public')
                                            ->imageEditor(),
                                    ])->columns(2),

                                Section::make('SEO — Analytics y verificaciones')
                                    ->schema([
                                        TextInput::make('seo_google_analytics')
                                            ->label('Google Analytics 4 ID')
                                            ->placeholder('G-XXXXXXXXXX')
                                            ->helperText('Empieza con G-. Lo encuentras en Google Analytics → Admin → Data Streams.'),
                                        TextInput::make('seo_google_search_console')
                                            ->label('Google Search Console — código de verificación')
                                            ->placeholder('ABC123xyz...')
                                            ->helperText('Solo el valor del atributo content de la meta tag de verificación.'),
                                        TextInput::make('seo_bing_verify')
                                            ->label('Bing Webmaster Tools — verificación')
                                            ->placeholder('ABC123...')
                                            ->helperText('Valor del atributo content de la meta tag de verificación de Bing.'),
                                        TextInput::make('seo_twitter_handle')
                                            ->label('Handle de Twitter / X')
                                            ->placeholder('@AeropuertoGYE')
                                            ->helperText('Incluye el @. Se usa en Twitter Cards.'),
                                    ])->columns(2),
                            ]),
                    ])->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    /** Mapea cada clave de setting a su sección legible para el log */
    protected static function settingSection(string $key): string
    {
        return match(true) {
            str_starts_with($key, 'site_')        => 'Identidad',
            str_starts_with($key, 'font_')        => 'Tipografías',
            str_starts_with($key, 'color_')       => 'Colores',
            in_array($key, ['dark_mode_enabled', 'default_theme']) => 'Tema',
            str_starts_with($key, 'contact_')     => 'Contacto',
            str_starts_with($key, 'social_')      => 'Redes Sociales',
            str_starts_with($key, 'header_') || str_starts_with($key, 'topbar_') => 'Header / CTA',
            str_starts_with($key, 'animations_')  => 'Animaciones',
            str_starts_with($key, 'mail_')        => 'Correo SMTP',
            str_starts_with($key, 'footer_') || str_starts_with($key, 'seo_') => 'Footer y SEO',
            default => 'General',
        };
    }

    public function save(): void
    {
        // Usar getState() (no $this->data) para que Filament ejecute el lifecycle
        // de FileUpload y mueva los temporales de Livewire al disk final ('public/branding').
        $state = $this->form->getState();

        // Capturar valores anteriores ANTES de guardar (para el log de auditoría)
        $oldSettings = SiteSetting::allCached();

        $assetKeys   = ['site_logo', 'site_logo_dark', 'site_logo_footer', 'site_favicon', 'seo_og_image'];
        $booleanKeys = ['dark_mode_enabled', 'header_cta_enabled', 'header_show_clock', 'topbar_enabled', 'animations_enabled', 'animations_on_mobile'];
        // Campos que NO se guardan directamente en settings
        $skipKeys    = ['mail_preset', 'mail_test_to'];

        foreach ($state as $key => $value) {
            // Ignorar campos auxiliares
            if (in_array($key, $skipKeys, true)) continue;

            // Contraseña SMTP: solo actualizar si el usuario escribió algo nuevo
            if ($key === 'mail_password') {
                if (empty($value)) continue;                   // sin cambios
                $value = Crypt::encryptString((string) $value); // cifrar
                SiteSetting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'string']);
                continue;
            }

            if (in_array($key, $assetKeys, true)) {
                // FileUpload single puede devolver array con un elemento — extraerlo.
                if (is_array($value)) {
                    $value = reset($value) ?: null;
                }
                $storedValue = is_string($value) ? $value : '';
                $type = 'string';
            } elseif (in_array($key, $booleanKeys, true)) {
                $storedValue = $value ? '1' : '0';
                $type = 'boolean';
            } elseif (is_array($value)) {
                $storedValue = json_encode($value);
                $type = 'json';
            } else {
                $storedValue = (string) ($value ?? '');
                $type = 'string';
            }

            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $storedValue, 'type' => $type]
            );
        }

        Cache::forget('site_settings');

        // ── Log de auditoría: registrar qué cambió exactamente ──────────────
        $changes  = [];
        $sections = [];

        foreach ($state as $key => $value) {
            if (in_array($key, $skipKeys) || $key === 'mail_password') continue;

            $oldRaw = $oldSettings[$key] ?? null;
            $newStr = is_array($value) ? json_encode($value) : (string) ($value ?? '');
            $oldStr = is_array($oldRaw)  ? json_encode($oldRaw)  : (string) ($oldRaw  ?? '');

            if ($oldStr !== $newStr) {
                $section = self::settingSection($key);
                $sections[$section] = true;
                // No loguear contraseñas aunque sean de otro campo
                $displayNew = str_contains($key, 'password') ? '••••••••' : $newStr;
                $displayOld = str_contains($key, 'password') ? '••••••••' : $oldStr;
                $changes[$key] = ['section' => $section, 'old' => $displayOld, 'new' => $displayNew];
            }
        }

        if (! empty($changes)) {
            $sectionList = implode(', ', array_keys($sections));
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['changes' => $changes])
                ->event('updated')
                ->log("Configuración actualizada — {$sectionList}");
        }

        Notification::make()->title('Configuracion guardada correctamente')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Guardar cambios')
                ->submit('save')
                ->icon('heroicon-o-check'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->hasAnyRole(['super_admin', 'admin']);
    }
}
