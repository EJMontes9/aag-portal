@props(['block'])
@php
    $items = $block->get('items', []);
    if (empty($items)) return;

    $bg = $block->get('background', 'bg');
    $bgClass = match($bg) {
        'soft' => 'bg-brand-soft',
        'navy' => 'bg-brand-navy text-on-navy',
        default => 'bg-bg',
    };
    $onNavy = $bg === 'navy';

    // Clases de rejilla ESTATICAS: Tailwind escanea el codigo fuente en tiempo
    // de build, asi que una clase construida en runtime ("md:grid-cols-{$n}")
    // nunca llega a compilarse. Hay que escribirlas literales.
    $cols = min(count($items), 4);
    $gridClass = match($cols) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        3 => 'grid-cols-2 md:grid-cols-3',
        default => 'grid-cols-2 md:grid-cols-4',
    };
@endphp

{{-- Cifras institucionales.

     La maqueta B no incluye ningun componente de estadisticas (el unico que
     existe entre las propuestas es el de la C). Se diseña aqui con el
     vocabulario de B: caja blanca, borde 1px marcado, radio 4px, sin sombra;
     cifra en Neulis Black navy y rotulo condensado en gris. --}}
<section class="{{ $bgClass }}">
    <div class="section-wrap">
        @if($block->get('title') || $block->get('kicker'))
            <div class="max-w-3xl mb-7">
                @if($block->get('kicker'))
                    <span class="kicker {{ $onNavy ? 'text-brand-accent' : '' }}">{{ $block->get('kicker') }}</span>
                @endif
                @if($block->get('title'))
                    <h2 class="font-serif text-section-title {{ $onNavy ? 'text-on-navy' : 'text-brand-navy' }} mt-2">{{ $block->get('title') }}</h2>
                @endif
                @if($block->get('subtitle'))
                    <p class="mt-3 text-sm {{ $onNavy ? 'text-on-navy/70' : 'text-muted' }} leading-relaxed max-w-2xl">{{ $block->get('subtitle') }}</p>
                @endif
            </div>
        @endif

        <div class="grid {{ $gridClass }} gap-4">
            @foreach($items as $item)
                <div class="{{ $onNavy ? 'border border-white/15 bg-white/[0.04]' : 'card-surface' }} rounded-card px-5 py-6 text-center"
                     data-stagger="stat" style="opacity:0;">
                    <div class="font-serif text-[32px] md:text-[38px] leading-none {{ $onNavy ? 'text-on-navy' : 'text-brand-navy' }} num-tabular"
                         @if(!empty($item['value'])) data-count-to="{{ $item['value'] }}" @endif>
                        {{ $item['value'] ?? '' }}
                    </div>
                    <div class="mt-2 font-sans text-[11px] font-bold uppercase tracking-[0.08em] {{ $onNavy ? 'text-on-navy/70' : 'text-muted' }} leading-tight">
                        {{ $item['label'] ?? '' }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
