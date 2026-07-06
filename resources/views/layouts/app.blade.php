@php
    $darkAllowed = (bool) settings('dark_mode_enabled', true);
    $defaultTheme = settings('default_theme', 'light');

    $animEnabled = (bool) settings('animations_enabled', true);
    $animSpeed = settings('animations_speed', 'normal');
    $animOnMobile = (bool) settings('animations_on_mobile', true);

    // ── Tema visual activo (layout header/footer + tokens de estilo) ──────────
    // Color y tipografia NO dependen del tema: siguen siendo globales (abajo).
    $siteTheme = \App\Support\Theme::active();
    $themeTokens = \App\Support\Theme::activeTokens();

    $fontSerif = settings('font_serif', 'Fraunces');
    $fontSans = settings('font_sans', 'Inter');
    $fontMono = settings('font_mono', 'JetBrains Mono');

    $encodeFontUrl = fn (string $family) => str_replace(' ', '+', $family);

    $navy = hex_to_rgb_tuple(settings('color_navy'), '11 30 74');
    $primary = hex_to_rgb_tuple(settings('color_primary'), '30 58 138');
    $accent = hex_to_rgb_tuple(settings('color_accent'), '91 143 217');
    $soft = hex_to_rgb_tuple(settings('color_soft'), '207 224 243');
    $bgLight = hex_to_rgb_tuple(settings('color_bg_light'), '250 250 251');
    $fgLight = hex_to_rgb_tuple(settings('color_fg_light'), '15 23 42');
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
      data-site-theme="{{ $siteTheme }}"
      data-radius="{{ $themeTokens['radius'] }}"
      data-density="{{ $themeTokens['density'] }}"
      data-gradients="{{ $themeTokens['gradients'] ? 'on' : 'off' }}"
      data-elevation="{{ $themeTokens['elevation'] }}"
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
         Se deduplican familias repetidas (ej. serif=sans=Inter, tipografia
         "uniforme" tipo Propuesta B) para no pedirle a Google Fonts la misma
         familia dos veces -- menos peso, menos requests. --}}
    @php
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
        $fontFamilies[$fontMono] = $monoSpec;

        $fontsUrl = 'https://fonts.googleapis.com/css2?' . collect($fontFamilies)
            ->map(fn ($spec, $family) => 'family=' . $encodeFontUrl($family) . ':' . $spec)
            ->implode('&') . '&display=swap';
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $fontsUrl }}" rel="stylesheet">

    {{-- ══ VARIABLES CSS ════════════════════════════════════════════════════════ --}}
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
            --color-muted: 100 116 139;
            --color-card: 255 255 255;
            --color-border: 226 232 240;
        }

        .dark {
            --color-bg: {{ $bgDark }};
            --color-fg: {{ $fgDark }};
            --color-muted: 148 163 184;
            --color-card: 22 28 48;
            --color-border: 39 48 72;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')

    {{-- ══ STRUCTURED DATA JSON-LD ══════════════════════════════════════════════
         Google usa esto para: Knowledge Panel, Rich Results, Breadcrumbs en SERPs
    ─────────────────────────────────────────────────────────────────────────── --}}

    {{-- Organization: presente en TODAS las páginas --}}
    <script type="application/ld+json">
    {!! json_encode($orgSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    {{-- WebPage genérico --}}
    <script type="application/ld+json">
    {!! json_encode([
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
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    @if($isHome)
    {{-- WebSite con SearchAction: solo en la homepage --}}
    <script type="application/ld+json">
    {!! json_encode($webSiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @endif

    {{-- Schemas específicos inyectados por cada página --}}
    @stack('json-ld')

</head>
<body class="min-h-screen bg-bg text-fg antialiased">
    <x-alerts.convocatoria-alert />
    <x-layout.header />

    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <x-layout.footer />

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
</body>
</html>
