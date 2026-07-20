@props(['block'])
@php
    $items = $block->get('items', []);
    if (empty($items)) return;

    $bg = $block->get('background', 'bg');
    $bgClass = match($bg) {
        'soft' => 'bg-brand-soft/30',
        'navy' => 'bg-brand-navy text-on-navy',
        default => 'bg-bg',
    };
    $kickerClass = $bg === 'navy' ? 'text-on-navy/60' : 'text-brand-primary';
    $titleClass = $bg === 'navy' ? 'text-on-navy' : 'text-brand-navy';
    $valueClass = $bg === 'navy' ? 'text-on-navy' : 'text-fg';
    $labelClass = $bg === 'navy' ? 'text-on-navy/70' : 'text-muted';
@endphp

<section class="{{ $bgClass }}">
    <div class="section-wrap">
        @if($block->get('title') || $block->get('kicker'))
            <div class="max-w-3xl mb-10">
                @if($block->get('kicker'))
                    <span class="font-sans text-[11px] tracking-[0.18em] uppercase {{ $kickerClass }} font-semibold">{{ $block->get('kicker') }}</span>
                @endif
                @if($block->get('title'))
                    <h2 class="font-serif text-section-title {{ $titleClass }} mt-3">{{ $block->get('title') }}</h2>
                @endif
                @if($block->get('subtitle'))
                    <p class="mt-4 {{ $kickerClass }} leading-[1.65] max-w-2xl">{{ $block->get('subtitle') }}</p>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-{{ min(count($items), 4) }} gap-8">
            @foreach($items as $item)
                <div class="flex flex-col gap-1.5">
                    <span class="font-serif text-[38px] md:text-[44px] font-normal {{ $valueClass }} leading-none tracking-[-0.02em]">{{ $item['value'] ?? '' }}</span>
                    <span class="font-sans text-[10px] tracking-[0.18em] uppercase {{ $labelClass }} font-semibold leading-tight">{{ $item['label'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
