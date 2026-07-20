@props(['block'])
@php
    $items = $block->get('items', []);
    if (empty($items)) return;
@endphp

{{-- Valores institucionales.

     Tampoco existe en la maqueta B. Se resuelve con su vocabulario: filas
     separadas por filete gris (no cajas flotantes), numero de orden en
     Neulis Black amarillo y texto condensado. Sin desplazamiento en hover:
     B es un diseño estatico. --}}
<section class="bg-bg">
    <div class="section-wrap grid lg:grid-cols-[1fr_1.15fr] gap-8 lg:gap-14">
        <div data-aos="fade-right">
            @if($block->get('kicker'))
                <span class="kicker">{{ $block->get('kicker') }}</span>
            @endif
            @if($block->get('title'))
                <h2 class="font-serif text-section-title text-brand-navy mt-2">
                    {!! italic_markdown($block->get('title')) !!}
                </h2>
            @endif
            @if($block->get('subtitle'))
                <p class="mt-4 text-sm text-muted max-w-[460px] leading-relaxed">{{ $block->get('subtitle') }}</p>
            @endif
        </div>

        <div class="bg-card border border-border rounded-card px-6">
            @foreach($items as $v)
                <div class="grid grid-cols-[52px_1fr] gap-5 items-baseline border-b border-border py-5 last:border-0"
                     data-stagger="value-row" style="opacity:0;">
                    <span class="font-serif text-[28px] text-brand-accent leading-none num-tabular">{{ $v['number'] ?? '' }}</span>
                    <div>
                        <h3 class="font-sans font-bold text-sm text-brand-navy uppercase tracking-[0.04em]">{{ $v['title'] ?? '' }}</h3>
                        @if(!empty($v['description']))
                            <p class="text-xs text-muted mt-1.5 leading-relaxed">{{ $v['description'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
