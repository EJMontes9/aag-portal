@props(['block'])

{{-- Estado de vuelos — Propuesta B.

     La columna derecha es una IMAGEN, no un tablero simulado.

     Las dos versiones anteriores fallaban por motivos distintos: la primera
     pintaba horarios de ejemplo, que un pasajero puede confundir con datos
     reales; la segunda los tapaba con un velo que repetia el mismo mensaje
     que ya esta en la columna de texto, a un palmo de distancia.

     La AAG no expone datos de vuelos propios —la fuente es el portal de
     TAGSA, al que lleva el boton—, asi que aqui no hay nada que tabular. Una
     imagen institucional acompana el mensaje sin competir con el y sin
     arriesgar que nadie lea una hora que no existe.

     La imagen se puede cambiar desde el admin; si no hay ninguna cargada se
     usa la del aeropuerto que viene con el proyecto. --}}
@php
    $imagen = $block->get('image');
    $imagenUrl = $imagen
        ? Storage::disk('public')->url($imagen)
        : asset('images/aeropuerto.webp');

    $imagenAlt = $block->get('image_alt')
        ?: 'Aeropuerto Internacional José Joaquín de Olmedo de Guayaquil';
@endphp

<section id="vuelos" class="bg-brand-navy text-on-navy">
    <div class="section-wrap grid lg:grid-cols-[1fr_1.1fr] gap-8 lg:gap-14 items-center">

        {{-- ── Columna de texto ──────────────────────────────────────────── --}}
        <div data-aos="fade-right" data-aos-duration="700">
            @if($block->get('kicker'))
                {{-- El kicker es celeste por defecto; sobre navy sube a amarillo
                     para despegar del fondo. --}}
                <span class="kicker text-brand-accent">{{ $block->get('kicker') }}</span>
            @endif
            @if($block->get('title'))
                <h2 class="font-serif text-section-title text-on-navy mt-2">{{ $block->get('title') }}</h2>
            @endif
            @if($block->get('subtitle'))
                <p class="mt-4 text-[15px] text-on-navy/80 max-w-[460px] leading-relaxed">{{ $block->get('subtitle') }}</p>
            @endif
            @if($block->get('cta_label'))
                <div class="mt-7 flex flex-wrap items-center gap-4">
                    {{-- Sobre fondo oscuro la accion principal de B es el amarillo. --}}
                    <a href="{{ $block->get('cta_url', '#') }}" target="_blank" rel="noopener" class="btn-white">
                        {{ $block->get('cta_label') }}
                        {{-- Icono de enlace externo: avisa de que se sale del portal. --}}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                    </a>
                    @if($block->get('cta_note'))
                        <span class="text-[13px] text-on-navy/70 max-w-[24ch]">{{ $block->get('cta_note') }}</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- ── Imagen ────────────────────────────────────────────────────── --}}
        {{-- Filete amarillo al pie, el mismo recurso que cierra la franja de
             marca del header: enmarca la imagen dentro del lenguaje de B sin
             recurrir a sombras. --}}
        <figure class="relative rounded-card overflow-hidden rule-accent bg-cloud-gradient"
                data-aos="fade-left" data-aos-duration="700" data-aos-delay="150">
            <img src="{{ $imagenUrl }}"
                 alt="{{ $imagenAlt }}"
                 loading="lazy" decoding="async"
                 class="w-full h-full object-cover aspect-[16/10]">
        </figure>
    </div>
</section>
