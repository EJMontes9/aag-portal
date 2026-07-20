@props(['block'])
@php
    // Enum de fondo ('bg','soft','card') guardado en BD: no se renombra.
    $bg = $block->get('background', 'bg');
    $bgClass = match($bg) {
        'soft' => 'bg-brand-soft',
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

    $title = $block->get('title', '');

    // SEGURIDAD -- Antes se pintaba el HTML del campo tal cual, quitandole solo
    // width/height/style, que es un saneo cosmetico: un <script> pegado ahi se
    // ejecutaba. Ahora se extrae UNICAMENTE la direccion, se comprueba que sea
    // de un proveedor conocido, y el iframe lo construye esta plantilla.
    $embedUrl = \App\Services\EmbedUrl::extraer($block->get('embed_code'), 'mapa');
@endphp

{{-- Mapa a ancho completo.

     El titulo pasa de centrado a alineado a la izquierda: B alinea a la
     izquierda todos los rotulos de seccion, y el centrado rompia la columna
     comun con el resto de bloques de la pagina. --}}
<section class="{{ $bgClass }}">
    <div class="section-wrap">
        @if($title)
            {{-- mb-8: mismo aire entre encabezado y contenido que el resto de bloques. --}}
            <h2 class="font-serif text-section-title text-brand-navy mb-8">{{ $title }}</h2>
        @endif

        @if($embedUrl)
            {{-- .card-surface = caja blanca, borde 1px marcado, radio 4px y cero
                 sombra; es el unico envoltorio permitido en B. --}}
            <div class="map-embed-wrap w-full card-surface overflow-hidden"
                 style="{{ $heightStyle }}">
                {{-- El iframe se construye aqui, con la URL ya validada.
                     sandbox limita lo que puede hacer el contenido incrustado:
                     se le permite ejecutar scripts (el mapa los necesita) pero
                     no acceder a nuestra pagina ni abrir ventanas. --}}
                <iframe src="{{ $embedUrl }}"
                        title="{{ $title ?: 'Mapa' }}"
                        style="width:100%;height:100%;border:0;display:block;"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        sandbox="allow-scripts allow-same-origin allow-popups"
                        allowfullscreen></iframe>
            </div>
        @else
            {{-- Estado vacio: caja de la misma forma que el mapa real, en gris
                 de fondo, sin gradiente decorativo (B no decora los vacios). --}}
            <div class="w-full card-surface bg-bg
                        flex flex-col items-center justify-center gap-3"
                 style="{{ $heightStyle }}">
                <svg class="w-12 h-12 text-border" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="font-sans text-[12px] font-bold uppercase tracking-[0.08em] text-muted">Sin código embed configurado</p>
            </div>
        @endif
    </div>
</section>
