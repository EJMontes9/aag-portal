@props(['block'])
@php
    $bg = $block->get('background', 'bg');
    $bgClass = match($bg) {
        'soft' => 'bg-brand-soft/30',
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

<section class="{{ $bgClass }}">
    <div class="section-wrap">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="@if($side === 'left') lg:order-2 @endif">
                @if($block->get('kicker'))
                    {{-- Kicker en azul primario, all-caps, bold — estilo Propuesta B --}}
                    <span class="font-sans text-[11px] tracking-[0.15em] uppercase text-brand-primary font-bold">{{ $block->get('kicker') }}</span>
                @endif
                @if($block->get('title'))
                    <h2 class="font-serif text-section-title text-brand-navy mt-3">{{ $block->get('title') }}</h2>
                @endif
                @if($block->get('body'))
                    <p class="mt-5 text-muted leading-[1.7] whitespace-pre-line">{{ $block->get('body') }}</p>
                @endif
                @if($block->get('cta_label'))
                    <a href="{{ $block->get('cta_url', '#') }}" class="btn-primary mt-7">{{ $block->get('cta_label') }}</a>
                @endif
            </div>

            <div class="@if($side === 'left') lg:order-1 @endif">
                @if($imageUrl)
                    <img src="{{ $imageUrl }}"
                         alt="{{ $block->get('title', '') }}"
                         class="w-full rounded-hero shadow-md"
                         loading="lazy">

                @elseif($cleanEmbed)
                    {{-- El contenedor usa aspect-ratio; el iframe se ajusta al 100% x 100% --}}
                    <div class="map-embed-wrap w-full overflow-hidden rounded-hero shadow-md"
                         style="aspect-ratio:4/3; min-height:260px;">
                        {!! $cleanEmbed !!}
                    </div>

                @else
                    <div class="w-full rounded-hero bg-gradient-to-br from-brand-soft to-brand-accent/40
                                flex items-center justify-center"
                         style="aspect-ratio:4/3; min-height:260px;">
                        <svg class="w-16 h-16 text-brand-accent/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
