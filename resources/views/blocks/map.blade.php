@props(['block'])
@php
    $bg = $block->get('background', 'bg');
    $bgClass = match($bg) {
        'soft' => 'bg-brand-soft/30',
        'card' => 'bg-card',
        default => 'bg-bg',
    };

    $height = $block->get('height', 'medium');
    $heightStyle = match($height) {
        'small'  => 'height:300px',
        'large'  => 'height:600px',
        'full'   => 'height:100vh',
        default  => 'height:450px',   // medium
    };

    $embedCode = $block->get('embed_code', '');
    $title     = $block->get('title', '');

    // Eliminar width/height/style del iframe y forzar que llene el contenedor
    $cleanEmbed = preg_replace('/\s+(width|height|style)\s*=\s*"[^"]*"/', '', $embedCode);
    $cleanEmbed = str_replace('<iframe', '<iframe style="width:100%;height:100%;border:0;display:block;"', $cleanEmbed);
@endphp

<section class="{{ $bgClass }}">
    <div class="section-wrap">
        @if($title)
            <h2 class="font-serif text-section-title text-fg text-center mb-8">{{ $title }}</h2>
        @endif

        @if($cleanEmbed)
            <div class="map-embed-wrap w-full overflow-hidden rounded-hero shadow-md"
                 style="{{ $heightStyle }}">
                {!! $cleanEmbed !!}
            </div>
        @else
            <div class="w-full rounded-hero bg-gradient-to-br from-brand-soft to-brand-accent/40
                        flex flex-col items-center justify-center gap-4"
                 style="{{ $heightStyle }}">
                <svg class="w-16 h-16 text-brand-accent/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-muted text-sm">Sin código embed configurado</p>
            </div>
        @endif
    </div>
</section>
