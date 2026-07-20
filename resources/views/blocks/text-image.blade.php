@props(['block'])
@php
    // Enum de fondo ('bg','soft','card') guardado en BD: no se renombra.
    $bg = $block->get('background', 'bg');
    $bgClass = match($bg) {
        'soft' => 'bg-brand-soft',
        'card' => 'bg-card',
        default => 'bg-bg',
    };
    $side     = $block->get('image_side', 'right');
    $imageUrl = $block->get('image') ? Storage::disk('public')->url($block->get('image')) : null;

    // Limpiar width/height/style del iframe e inyectar estilos para llenar el contenedor
    $rawEmbed   = $block->get('map_embed', '');
    $cleanEmbed = '';
    if ($rawEmbed) {
        $cleanEmbed = preg_replace('/\s+(width|height|style)\s*=\s*"[^"]*"/', '', $rawEmbed);
        $cleanEmbed = str_replace('<iframe', '<iframe style="width:100%;height:100%;border:0;display:block;"', $cleanEmbed);
    }
@endphp

{{-- Texto + imagen (o mapa).

     El medio se trata como una caja de B: borde gris de 1px, radio 4px y cero
     sombra. La sombra anterior era el rasgo mas visible de la Propuesta A. --}}
<section class="{{ $bgClass }}">
    <div class="section-wrap">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <div class="@if($side === 'left') lg:order-2 @endif">
                @if($block->get('kicker'))
                    {{-- .kicker ya define familia, 11px, mayusculas, tracking y
                         color celeste: reconstruirlo a mano lo desincroniza del
                         resto del sitio cuando se retoque el API de diseno. --}}
                    <span class="kicker">{{ $block->get('kicker') }}</span>
                @endif
                @if($block->get('title'))
                    <h2 class="font-serif text-section-title text-brand-navy mt-2">{{ $block->get('title') }}</h2>
                @endif
                @if($block->get('body'))
                    {{-- Es el cuerpo de lectura del bloque, no un pie: 15px y
                         medida maxima para no pasar de ~75 caracteres por linea. --}}
                    <p class="mt-4 text-[15px] text-muted leading-relaxed whitespace-pre-line max-w-[62ch]">{{ $block->get('body') }}</p>
                @endif
                @if($block->get('cta_label'))
                    <a href="{{ $block->get('cta_url', '#') }}" class="btn-primary mt-6">{{ $block->get('cta_label') }}</a>
                @endif
            </div>

            <div class="@if($side === 'left') lg:order-1 @endif">
                @if($imageUrl)
                    <img src="{{ $imageUrl }}"
                         alt="{{ $block->get('title', '') }}"
                         class="w-full rounded-card border border-border block"
                         loading="lazy">

                @elseif($cleanEmbed)
                    {{-- El contenedor usa aspect-ratio; el iframe se ajusta al 100% x 100% --}}
                    <div class="map-embed-wrap w-full card-surface overflow-hidden"
                         style="aspect-ratio:4/3; min-height:260px;">
                        {!! $cleanEmbed !!}
                    </div>

                @else
                    {{-- Placeholder sin foto: el gradiente navy->celeste es el
                         relleno que usa B para los contenedores de imagen vacios. --}}
                    <div class="w-full rounded-card border border-border bg-cloud-gradient
                                flex items-center justify-center"
                         style="aspect-ratio:4/3; min-height:260px;">
                        <svg class="w-14 h-14 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
