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

        {{-- Cada cifra va rematada por el filete amarillo superior, el mismo
             recurso que marca las bandas del header. Da jerarquia sin recurrir
             a sombras ni a fondos de color, que no pertenecen a B. --}}
        <div class="grid {{ $gridClass }} gap-4">
            @foreach($items as $item)
                @php $cc = parse_stat_value_for_animation($item['value'] ?? ''); @endphp
                <div class="relative overflow-hidden {{ $onNavy ? 'border border-white/15 bg-white/[0.04]' : 'card-surface' }} rounded-card px-5 py-7 text-center"
                     data-stagger="stat" style="opacity:0;">
                    <span class="absolute top-0 left-0 h-[3px] w-full bg-brand-accent" aria-hidden="true"></span>

                    <div class="font-serif text-[38px] md:text-[44px] leading-none {{ $onNavy ? 'text-on-navy' : 'text-brand-navy' }} num-tabular"
                         @if($cc) data-count-to="{{ $cc['target'] }}" data-count-format="{{ $cc['format'] }}" @if(!empty($cc['suffix'])) data-count-suffix="{{ $cc['suffix'] }}" @endif @endif>
                        {{ $item['value'] ?? '' }}
                    </div>
                    <div class="mt-2.5 font-sans text-xs font-bold uppercase tracking-[0.08em] {{ $onNavy ? 'text-on-navy/75' : 'text-muted' }} leading-tight">
                        {{ $item['label'] ?? '' }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
