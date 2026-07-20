@props(['block'])
@php
    $items = $block->get('items', []);
    if (empty($items)) return;
@endphp

<section class="bg-bg">
    <div class="section-wrap grid lg:grid-cols-[1fr_1.15fr] gap-16">
        <div data-aos="fade-right">
            @if($block->get('kicker'))
                <span class="font-sans text-[11px] tracking-[0.15em] uppercase text-brand-primary font-bold">{{ $block->get('kicker') }}</span>
            @endif
            @if($block->get('title'))
                <h2 class="font-serif text-section-title text-brand-navy mt-4">
                    {!! italic_markdown($block->get('title')) !!}
                </h2>
            @endif
            @if($block->get('subtitle'))
                <p class="mt-6 text-muted max-w-[420px] leading-[1.7]">{{ $block->get('subtitle') }}</p>
            @endif
        </div>
        <div>
            @foreach($items as $v)
                <div class="grid grid-cols-[90px_1fr] gap-8 items-baseline border-b border-border py-7 first:pt-0 last:border-0 last:pb-0 group transition-all duration-300 hover:pl-2"
                     data-stagger="value-row" style="opacity:0;">
                    <span class="font-serif text-[44px] text-brand-accent/80 leading-none transition-all duration-500 group-hover:text-brand-primary group-hover:scale-110 origin-left" style="font-weight:400;">{{ $v['number'] ?? '' }}</span>
                    <div class="pt-2">
                        <h3 class="font-sans font-semibold text-[15px] text-fg">{{ $v['title'] ?? '' }}</h3>
                        @if(!empty($v['description']))
                            <p class="text-sm text-muted mt-2 leading-[1.65]">{{ $v['description'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
