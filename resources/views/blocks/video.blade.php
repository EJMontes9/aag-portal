@props(['block'])
@php
    $url      = $block->get('video_url', '');
    $videoId  = null;
    $provider = null;  // 'youtube' | 'vimeo'

    // ── Detectar proveedor y extraer ID ─────────────────────────────────────
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w\-]+)~', $url, $m)) {
        $videoId  = $m[1];
        $provider = 'youtube';
    } elseif (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
        $videoId  = $m[1];
        $provider = 'vimeo';
    }

    // ── Opciones de reproducción ─────────────────────────────────────────────
    $autoplay        = (bool) $block->get('autoplay', false);
    $mute            = (bool) $block->get('mute', false)  || $autoplay; // autoplay fuerza mute
    $loop            = (bool) $block->get('loop', false);
    $controls        = (bool) $block->get('controls', true);
    $rel             = (bool) $block->get('rel', false);
    $modestbranding  = (bool) $block->get('modestbranding', true);
    $startMin        = (int)  $block->get('start_min', 0);
    $startSec        = (int)  $block->get('start_sec', 0);
    $startTotal      = $startMin * 60 + $startSec;  // segundos totales para YouTube

    // ── Construir URL de embed ───────────────────────────────────────────────
    $embed = null;

    if ($provider === 'youtube' && $videoId) {
        $params = [
            'autoplay'       => $autoplay        ? 1 : 0,
            'mute'           => $mute            ? 1 : 0,
            'loop'           => $loop            ? 1 : 0,
            'controls'       => $controls        ? 1 : 0,
            'rel'            => $rel             ? 1 : 0,
            'modestbranding' => $modestbranding  ? 1 : 0,
        ];
        if ($loop) {
            // YouTube requiere playlist=VIDEOID para que loop funcione en embed
            $params['playlist'] = $videoId;
        }
        if ($startTotal > 0) {
            $params['start'] = $startTotal;
        }
        $embed = 'https://www.youtube.com/embed/' . $videoId . '?' . http_build_query($params);
    }

    if ($provider === 'vimeo' && $videoId) {
        $params = [
            'autoplay' => $autoplay  ? 1 : 0,
            'muted'    => $mute      ? 1 : 0,
            'loop'     => $loop      ? 1 : 0,
            'controls' => $controls  ? 1 : 0,
            'title'    => 0,
            'byline'   => 0,
            'portrait' => 0,
        ];
        // Vimeo: tiempo de inicio en formato "1m30s" o "90s"
        if ($startTotal > 0) {
            $params['t'] = $startMin > 0
                ? "{$startMin}m{$startSec}s"
                : "{$startSec}s";
        }
        $embed = 'https://player.vimeo.com/video/' . $videoId . '?' . http_build_query($params);
    }

    // SEGURIDAD -- Si no se reconocio YouTube ni Vimeo, antes se ponia la URL
    // TAL CUAL en el src del iframe. Eso permitia empotrar cualquier sitio de
    // terceros dentro del portal (phishing con apariencia oficial) y, segun el
    // navegador, esquemas como data: o javascript:.
    // Ahora esa URL de reserva tambien pasa por la lista de proveedores; si no
    // esta, no se renderiza nada.
    if (! $embed && $url) {
        $embed = \App\Services\EmbedUrl::extraer($url, 'video');
    }

    // ── Estilos de fondo ─────────────────────────────────────────────────────
    // Enum ('bg','soft','navy') guardado en BD: solo cambian las clases.
    $bg      = $block->get('background', 'bg');
    $bgClass = match($bg) {
        'soft'  => 'bg-brand-soft',
        'navy'  => 'bg-brand-navy text-on-navy',
        default => 'bg-bg',
    };
    $onNavy = $bg === 'navy';

    // Sobre navy el celeste del .kicker pierde contraste, asi que se sustituye
    // por el amarillo institucional (mismo criterio que el bloque stats).
    $kickerClass   = $onNavy ? 'text-brand-accent'  : '';
    $titleClass    = $onNavy ? 'text-on-navy'       : 'text-brand-navy';
    $subtitleClass = $onNavy ? 'text-on-navy/75'    : 'text-muted';
    // El marco del reproductor sigue siendo una caja de B (borde 1px, radio
    // 4px, sin sombra); sobre navy el borde gris no se ve, se aclara.
    $frameClass    = $onNavy ? 'border border-white/20' : 'card-surface';

    // Ancho del reproductor, configurable desde el editor de bloques.
    $width      = $block->get('width', 'lg');
    $widthClass = match($width) {
        'sm'    => 'max-w-lg',
        'md'    => 'max-w-3xl',
        'full'  => 'max-w-none',
        default => 'max-w-5xl',
    };

    // ── Permisos del iframe ───────────────────────────────────────────────────
    $allow = implode('; ', array_filter([
        'accelerometer',
        $autoplay ? 'autoplay' : null,
        'clipboard-write',
        'encrypted-media',
        'gyroscope',
        'picture-in-picture',
        'web-share',
    ]));
@endphp

<section class="{{ $bgClass }}">
    <div class="section-wrap">

        {{-- Encabezado del bloque --}}
        @if($block->get('kicker') || $block->get('title') || $block->get('subtitle'))
            <div class="max-w-3xl mb-8">
                @if($block->get('kicker'))
                    {{-- .kicker centraliza familia, 11px, mayúsculas y tracking. --}}
                    <span class="kicker {{ $kickerClass }}">
                        {{ $block->get('kicker') }}
                    </span>
                @endif
                @if($block->get('title'))
                    <h2 class="font-serif text-section-title {{ $titleClass }} mt-2">
                        {{ $block->get('title') }}
                    </h2>
                @endif
                @if($block->get('subtitle'))
                    <p class="mt-3 text-[15px] {{ $subtitleClass }} leading-relaxed">
                        {{ $block->get('subtitle') }}
                    </p>
                @endif
            </div>
        @endif

        {{-- Reproductor.
             Sin mx-auto: el video se alinea a la izquierda con el encabezado,
             que es la columna de lectura del resto de bloques. --}}
        @if($embed)
            <div class="{{ $frameClass }} overflow-hidden aspect-video {{ $widthClass }}">
                <iframe
                    src="{{ $embed }}"
                    class="w-full h-full"
                    frameborder="0"
                    allow="{{ $allow }}"
                    allowfullscreen
                    loading="lazy"
                    title="{{ $block->get('title', 'Video') }}"
                ></iframe>
            </div>
        @endif

    </div>
</section>
