@php
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

    // Mismos tokens del tema activo que usa el layout público
    $themeTokens = \App\Support\Theme::activeTokens();
@endphp
<!DOCTYPE html>
<html lang="es"
      data-theme-allowed="false"
      data-theme-default="light"
      data-anim-enabled="false"
      data-anim-speed="normal"
      data-anim-mobile="true"
      data-radius="{{ $themeTokens['radius'] }}"
      data-density="{{ $themeTokens['density'] }}"
      data-gradients="{{ $themeTokens['gradients'] ? 'on' : 'off' }}"
      data-elevation="{{ $themeTokens['elevation'] }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Editor visual' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $encodeFontUrl($fontSerif) }}:ital,wght@0,400;0,500;0,600;1,400;1,500&family={{ $encodeFontUrl($fontSans) }}:wght@400;500;600;700&family={{ $encodeFontUrl($fontMono) }}:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- IMPORTANTE: después de @vite para sobreescribir defaults del CSS compilado --}}
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

            --color-bg: {{ $bgLight }};
            --color-fg: {{ $fgLight }};
            --color-muted: 100 116 139;
            --color-card: 255 255 255;
            --color-border: 226 232 240;
        }
    </style>
    @livewireStyles
</head>
<body class="bg-bg text-fg antialiased">
    {{ $slot ?? '' }}

    {{-- Media Picker: modal global disponible en todo el editor visual --}}
    @livewire('media-picker')

    @livewireScripts
</body>
</html>
