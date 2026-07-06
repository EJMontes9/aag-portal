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

    // Si pegaron directamente una URL de embed u otro formato
    if (! $embed && $url) {
        $embed = $url;
    }

    // ── Estilos de fondo ─────────────────────────────────────────────────────
    $bg      = $block->get('background', 'bg');
    $bgClass = match($bg) {
        'soft'  => 'bg-brand-soft/30',
        'navy'  => 'bg-brand-navy text-on-navy',
        default => 'bg-bg',
    };
    $kickerClass   = $bg === 'navy' ? 'text-on-navy/60'  : 'text-muted';
    $titleClass    = $bg === 'navy' ? 'text-on-navy'     : 'text-fg';
    $subtitleClass = $bg === 'navy' ? 'text-on-navy/75'  : 'text-muted';

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
            <div class="max-w-3xl mb-10">
                @if($block->get('kicker'))
                    <span class="font-sans text-[11px] tracking-[0.18em] uppercase {{ $kickerClass }} font-semibold">
                        {{ $block->get('kicker') }}
                    </span>
                @endif
                @if($block->get('title'))
                    <h2 class="font-serif text-section-title {{ $titleClass }} mt-3">
                        {{ $block->get('title') }}
                    </h2>
                @endif
                @if($block->get('subtitle'))
                    <p class="mt-4 {{ $subtitleClass }} leading-[1.65]">
                        {{ $block->get('subtitle') }}
                    </p>
                @endif
            </div>
        @endif

        {{-- Reproductor --}}
        @if($embed)
            <div class="rounded-hero overflow-hidden border border-border shadow-lg aspect-video max-w-5xl mx-auto">
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
