@php
    $darkAllowed = (bool) settings('dark_mode_enabled', true);
    $defaultTheme = settings('default_theme', 'light');

    $textSizeAllowed = (bool) settings('text_size_control_enabled', true);
    $defaultTextSize = settings('default_text_size', 'normal');

    $animEnabled = (bool) settings('animations_enabled', true);
    $animSpeed = settings('animations_speed', 'normal');
    $animOnMobile = (bool) settings('animations_on_mobile', true);

    $fontSerif = settings('font_serif', 'Fraunces');
    $fontSans = settings('font_sans', 'Inter');
    $fontMono = settings('font_mono', 'JetBrains Mono');

    $encodeFontUrl = fn (string $family) => str_replace(' ', '+', $family);

    $navy = hex_to_rgb_tuple(settings('color_navy'), '46 47 99');
    $primary = hex_to_rgb_tuple(settings('color_primary'), '0 156 223');
    $accent = hex_to_rgb_tuple(settings('color_accent'), '239 198 0');
    $soft = hex_to_rgb_tuple(settings('color_soft'), '229 244 251');
    $bgLight = hex_to_rgb_tuple(settings('color_bg_light'), '245 245 245');
    $fgLight = hex_to_rgb_tuple(settings('color_fg_light'), '34 34 34');
    $bgDark = hex_to_rgb_tuple(settings('color_bg_dark'), '11 15 30');
    $fgDark = hex_to_rgb_tuple(settings('color_fg_dark'), '226 232 240');

    // ── SEO variables ────────────────────────────────────────────────────────
    $pageTitle      = $title ?? settings('seo_meta_title', settings('site_name', 'AAG Portal'));
    $pageDesc       = $description ?? settings('seo_meta_description', '');
    $siteName       = settings('site_name', 'Autoridad Aeroportuaria de Guayaquil');
    $canonical      = url()->current();
    $ogImage        = $ogImage ?? setting_asset('seo_og_image') ?? setting_asset('site_og_image') ?? setting_asset('site_logo');
    $twitterHandle  = settings('seo_twitter_handle', '');
    $gscVerify      = settings('seo_google_search_console', '');
    $bingVerify     = settings('seo_bing_verify', '');
    $isHome         = request()->routeIs('home');

    // Separador de título: "Página | Sitio"
    $fullTitle = ($isHome || $pageTitle === $siteName)
        ? $siteName
        : $pageTitle . ' | ' . $siteName;

    // Datos de la organización para JSON-LD
    $orgName    = $siteName;
    $orgUrl     = url('/');
    $orgLogo    = setting_asset('site_logo');
    $orgAddress = settings('contact_address', '');
    $orgPhone   = settings('contact_phone', '');
    $orgEmail   = settings('contact_email', '');
    $socialFb   = settings('social_facebook', '');
    $socialTw   = settings('social_twitter', '');
    $socialIg   = settings('social_instagram', '');
    $socialYt   = settings('social_youtube', '');
    $socialLi   = settings('social_linkedin', '');
    $sameAs     = array_values(array_filter([$socialFb, $socialTw, $socialIg, $socialYt, $socialLi]));

    // JSON-LD: Organization (presente en todas las páginas)
    $orgSchema = [
        '@context' => 'https://schema.org',
        '@type'    => 'GovernmentOrganization',
        '@id'      => $orgUrl . '#organization',
        'name'     => $orgName,
        'url'      => $orgUrl,
        'logo'     => $orgLogo ? [
            '@type'  => 'ImageObject',
            'url'    => $orgLogo,
            'width'  => 240,
            'height' => 60,
        ] : null,
        'address' => $orgAddress ? [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $orgAddress,
            'addressLocality' => 'Guayaquil',
            'addressCountry'  => 'EC',
        ] : null,
        'telephone'      => $orgPhone ?: null,
        'email'          => $orgEmail ?: null,
        'sameAs'         => $sameAs ?: null,
    ];
    // Limpiar nulls del schema
    $orgSchema = array_filter($orgSchema, fn($v) => $v !== null && $v !== [] && $v !== '');

    // JSON-LD: WebSite (solo en homepage — activa sitelinks searchbox en Google)
    $webSiteSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'WebSite',
        '@id'             => $orgUrl . '#website',
        'name'            => $siteName,
        'url'             => $orgUrl,
        'inLanguage'      => 'es-EC',
        'publisher'       => ['@id' => $orgUrl . '#organization'],
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => $orgUrl . 'noticias?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="es"
      data-theme-allowed="{{ $darkAllowed ? 'true' : 'false' }}"
      data-theme-default="{{ $defaultTheme }}"
      data-anim-enabled="{{ $animEnabled ? 'true' : 'false' }}"
      data-anim-speed="{{ $animSpeed }}"
      data-anim-mobile="{{ $animOnMobile ? 'true' : 'false' }}"
      data-textsize-allowed="{{ $textSizeAllowed ? 'true' : 'false' }}"
      data-textsize-default="{{ $defaultTextSize }}"
      class="{{ $defaultTheme === 'dark' && $darkAllowed ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ══ TÍTULO Y META DESCRIPCIÓN ══════════════════════════════════════════ --}}
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ Str::limit(strip_tags($pageDesc), 160) }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Idioma y región --}}
    <link rel="alternate" hreflang="es-EC" href="{{ $canonical }}">
    <link rel="alternate" hreflang="es" href="{{ $canonical }}">
    <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">

    {{-- ══ CONTROL DE INDEXACIÓN ════════════════════════════════════════════════ --}}
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

    {{-- ══ VERIFICACIONES DE PROPIETARIO ═══════════════════════════════════════ --}}
    @if($gscVerify)
        <meta name="google-site-verification" content="{{ $gscVerify }}">
    @endif
    @if($bingVerify)
        <meta name="msvalidate.01" content="{{ $bingVerify }}">
    @endif

    {{-- ══ OPEN GRAPH (Facebook, LinkedIn, WhatsApp, Slack…) ══════════════════ --}}
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($pageDesc), 200) }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="es_EC">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ $pageTitle }}">
    @endif
    @stack('og-meta')

    {{-- ══ TWITTER CARD ════════════════════════════════════════════════════════ --}}
    <meta name="twitter:card" content="summary_large_image">
    @if($twitterHandle)
        <meta name="twitter:site" content="{{ $twitterHandle }}">
    @endif
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($pageDesc), 200) }}">
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
        <meta name="twitter:image:alt" content="{{ $pageTitle }}">
    @endif

    {{-- ══ FAVICON ══════════════════════════════════════════════════════════════ --}}
    @if(setting_asset('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ setting_asset('site_favicon') }}">
        <link rel="shortcut icon" href="{{ setting_asset('site_favicon') }}">
    @endif
    @if(setting_asset('site_logo'))
        <link rel="apple-touch-icon" href="{{ setting_asset('site_logo') }}">
    @endif

    {{-- ══ RSS FEED (noticias) ══════════════════════════════════════════════════ --}}
    <link rel="alternate" type="application/rss+xml"
          title="{{ $siteName }} — Noticias"
          href="{{ route('news.index') }}?format=rss">

    {{-- ══ FUENTES ══════════════════════════════════════════════════════════════
         Se deduplican familias repetidas (ej. serif=sans=Inter, tipografía
         "uniforme" tipo Propuesta B) para no pedirle a Google Fonts la misma
         familia dos veces -- menos peso, menos requests.
         Las fuentes propias de marca (no están en Google Fonts) se sirven
         locales vía @font-face en app.css y se excluyen de esta petición. --}}
    @php
        // Fuentes de marca AAG auto-hospedadas (ver @font-face en resources/css/app.css).
        // Barlow Condensed SÍ existe en Google Fonts, pero se sirve local para que
        // el diseño no dependa de una petición externa: si no carga, el fallback
        // no es condensado y descuadra todo el ritmo horizontal de la Propuesta B.
        $selfHostedFonts = ['Neulis Black', 'Barlow Condensed'];

        $serifSpec = $fontSerif === 'Fraunces'
            ? 'ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400;1,9..144,500'
            : 'ital,wght@0,400;0,500;0,600;1,400;1,500';
        $sansSpec = 'wght@400;500;600;700';
        $monoSpec = 'wght@400;500;600';

        $fontFamilies = [];
        // Si serif y sans son la misma familia, se unifica en una sola entrada
        // combinando ambas especificaciones (cubre cursivas de titulares + pesos de UI).
        if ($fontSerif === $fontSans) {
            $fontFamilies[$fontSerif] = 'ital,wght@0,400;0,500;0,600;0,700;1,400;1,500';
        } else {
            $fontFamilies[$fontSerif] = $serifSpec;
            $fontFamilies[$fontSans] = $sansSpec;
        }

        // La mono NO se pide a Google: ninguna plantilla del front público usa
        // ya la clase font-mono (las cifras van con .num-tabular sobre la
        // familia de marca). Cargarla era una petición externa por nada, con
        // su coste de red y de privacidad. La variable CSS se sigue definiendo
        // más abajo y cae a la monoespaciada del sistema si algo la usara.

        // Quitar las que se auto-hospedan -- no existen en Google Fonts.
        $googleFontFamilies = collect($fontFamilies)->except($selfHostedFonts);

        $fontsUrl = $googleFontFamilies->isNotEmpty()
            ? 'https://fonts.googleapis.com/css2?' . $googleFontFamilies
                ->map(fn ($spec, $family) => 'family=' . $encodeFontUrl($family) . ':' . $spec)
                ->implode('&') . '&display=swap'
            : null;
    @endphp
    @if($fontsUrl)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $fontsUrl }}" rel="stylesheet">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ══ VARIABLES CSS ════════════════════════════════════════════════════════
         IMPORTANTE: este <style> va DESPUÉS de @vite para que los valores
         dinámicos de la BD (colores, fuentes) sobreescriban los defaults
         compilados en app.css. No mover antes del @vite. --}}
    <style>
        :root {
            --font-serif: '{{ $fontSerif }}', ui-serif, Georgia, serif;
            --font-sans: '{{ $fontSans }}', ui-sans-serif, system-ui, sans-serif;
            --font-mono: '{{ $fontMono }}', ui-monospace, monospace;

            --color-navy: {{ $navy }};
            --color-primary: {{ $primary }};
            --color-accent: {{ $accent }};
            --color-soft: {{ $soft }};
            --color-on-navy: 255 255 255;
            --color-on-primary: 255 255 255;
            --color-on-accent: {{ contrast_text_tuple(settings('color_accent')) }};

            --color-bg: {{ $bgLight }};
            --color-fg: {{ $fgLight }};
            --color-muted: 102 102 102;  /* #666 */
            --color-card: 255 255 255;
            --color-border: 204 204 204; /* #CCC -- borde marcado de la Propuesta B */
        }

        .dark {
            --color-bg: {{ $bgDark }};
            --color-fg: {{ $fgDark }};
            --color-muted: 148 163 184;
            --color-card: 22 28 48;
            --color-border: 39 48 72;
        }
    </style>

    @stack('head')

    {{-- Runtime de Livewire: necesario para wire:navigate (transiciones SPA
         entre páginas sin recargar head/header/footer) aunque la página no
         tenga ningún componente Livewire propio. Ver app.js: la detección
         `hasLivewire` evita cargar una segunda instancia de Alpine cuando
         esto ya está presente. --}}
    @livewireStyles

    {{-- ══ STRUCTURED DATA JSON-LD ══════════════════════════════════════════════
         Google usa esto para: Knowledge Panel, Rich Results, Breadcrumbs en SERPs
    ─────────────────────────────────────────────────────────────────────────── --}}

    {{-- Organization: presente en TODAS las páginas --}}
    <script type="application/ld+json">
    {!! json_ld($orgSchema) !!}
    </script>

    {{-- WebPage genérico --}}
    <script type="application/ld+json">
    {!! json_ld([
        '@context'  => 'https://schema.org',
        '@type'     => 'WebPage',
        '@id'       => $canonical . '#webpage',
        'url'       => $canonical,
        'name'      => $fullTitle,
        'description' => Str::limit(strip_tags($pageDesc), 160),
        'inLanguage'  => 'es-EC',
        'isPartOf'    => ['@id' => $orgUrl . '#website'],
        'about'       => ['@id' => $orgUrl . '#organization'],
        'dateModified' => now()->toIso8601String(),
    ]) !!}
    </script>

    @if($isHome)
    {{-- WebSite con SearchAction: solo en la homepage --}}
    <script type="application/ld+json">
    {!! json_ld($webSiteSchema) !!}
    </script>
    @endif

    {{-- Schemas específicos inyectados por cada página --}}
    @stack('json-ld')

</head>
<body class="min-h-screen bg-bg text-fg antialiased">
    {{-- ══ SALTAR AL CONTENIDO ═══════════════════════════════════════════════════
         Requisito WCAG 2.1 AA (2.4.1 Evitar bloques). Quien navega con teclado o
         lector de pantalla tendría que pasar por todo el menú en CADA página
         antes de llegar al contenido; esto lo salta de un tabulador.

         Permanece oculto hasta que recibe el foco: es el primer elemento
         enfocable del documento, así que basta pulsar Tab nada más cargar.
         No se usa 'hidden' ni display:none porque eso lo sacaría del orden de
         tabulación y dejaría de cumplir su función. --}}
    <a href="#contenido"
       class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[100]
              focus:bg-brand focus:text-white focus:px-4 focus:py-2 focus:rounded
              focus:outline-none focus:ring-2 focus:ring-brand-accent">
        Saltar al contenido
    </a>

    <x-alerts.convocatoria-alert />

    {{-- @persist: con wire:navigate, Livewire NO destruye ni vuelve a montar
         estos nodos entre páginas -- los saca del DOM viejo y los reinserta
         en el nuevo tal cual estaban (con su estado de Alpine intacto: menú
         móvil abierto/cerrado, reloj de Guayaquil sin reiniciarse, etc). Es
         justo lo que hace que solo se sienta "recargar" el contenido de
         adentro, no la página completa. --}}
    @persist('header')
        <x-layout.header />
    @endpersist

    <main id="contenido" tabindex="-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @persist('footer')
        <x-layout.footer />
    @endpersist

    {{-- Barra de edición flotante — solo visible para editores autenticados --}}
    <x-editor-toolbar :editablePage="$editablePage ?? null" />

    @if(settings('seo_google_analytics'))
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ settings('seo_google_analytics') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ settings('seo_google_analytics') }}', { anonymize_ip: true });
        </script>
    @endif

    @livewireScripts
</body>
</html>
