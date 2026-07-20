@props(['block'])
@php
    // Los valores enum ('navy','primary','soft','card') viven en la BD y estan
    // sincronizados con CtaBlock::filamentBlock(): solo se reescriben las
    // clases Tailwind, nunca las claves ni los valores.
    $bg = $block->get('background', 'navy');
    $bgClass = match($bg) {
        'primary' => 'bg-brand-primary text-on-primary',
        'soft'    => 'bg-brand-soft',
        'card'    => 'bg-bg',
        default   => 'bg-brand-navy text-on-navy',
    };

    $onDark = in_array($bg, ['navy', 'primary'], true);

    // Sobre fondo claro el CTA no puede ser una banda de color (quedaria un
    // rectangulo plano sin jerarquia): se resuelve como caja blanca con borde
    // marcado y filete amarillo, que es el mecanismo de separacion de B.
    // Sobre fondo oscuro la propia banda hace de caja y el filete va abajo.
    $boxClass = $onDark ? '' : 'card-surface rule-accent px-6 py-8 md:px-10 md:py-9';

    $titleClass    = $onDark ? 'text-on-navy' : 'text-brand-navy';
    $subtitleClass = $onDark ? 'text-on-navy/75' : 'text-muted';

    // En B el amarillo es EL color de accion sobre fondo oscuro (.btn-white) y
    // el navy solido sobre fondo claro (.btn-primary). El boton blanco de la
    // version anterior venia de la Propuesta A, donde el criterio era inverso.
    $buttonClass = $onDark ? 'btn-white' : 'btn-primary';

    $align    = $block->get('align', 'center');
    $centered = $align === 'center';
@endphp

{{-- Llamado a la accion.

     Alineado a la izquierda el titulo y el boton se reparten en una franja
     horizontal (mas denso, que es la firma de B); centrado se apilan. --}}
<section class="{{ $bgClass }} {{ $onDark ? 'rule-accent' : '' }}">
    <div class="section-wrap">
        <div class="{{ $boxClass }}">
            <div class="{{ $centered ? 'text-center mx-auto max-w-3xl' : 'lg:flex lg:items-end lg:justify-between lg:gap-10' }}">
                <div class="{{ $centered ? '' : 'max-w-2xl' }}">
                    @if($block->get('title'))
                        <h2 class="font-serif text-section-title {{ $titleClass }}">{{ $block->get('title') }}</h2>
                    @endif
                    @if($block->get('subtitle'))
                        {{-- Cuerpo a 15px: es el texto que sostiene la llamada a la
                             accion, no un metadato. --}}
                        <p class="mt-3.5 text-[15px] {{ $subtitleClass }} leading-relaxed">{{ $block->get('subtitle') }}</p>
                    @endif
                </div>

                @if($block->get('cta_label'))
                    <div class="{{ $centered ? 'mt-7' : 'mt-6 lg:mt-0 lg:shrink-0' }}">
                        <a href="{{ $block->get('cta_url', '#') }}" class="{{ $buttonClass }}">{{ $block->get('cta_label') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
