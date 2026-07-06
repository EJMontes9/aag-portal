@props(['block'])
@php
    $bg = $block->get('background', 'navy');
    $bgClass = match($bg) {
        'primary' => 'bg-brand-primary text-on-primary',
        'soft' => 'bg-brand-soft/30 text-fg',
        'card' => 'bg-card text-fg border border-border',
        default => 'bg-brand-navy text-on-navy',
    };
    $titleClass = in_array($bg, ['navy', 'primary']) ? 'text-white' : 'text-fg';
    $subtitleClass = in_array($bg, ['navy', 'primary']) ? 'text-white/80' : 'text-muted';
    $buttonClass = in_array($bg, ['navy', 'primary'])
        ? 'inline-flex items-center justify-center gap-2 rounded-pill bg-white text-brand-navy px-6 py-3 text-sm font-medium transition hover:-translate-y-px'
        : 'btn-primary';
    $align = $block->get('align', 'center');
    $alignClass = $align === 'center' ? 'text-center mx-auto' : '';
@endphp

<section class="{{ $bgClass }}">
    <div class="section-wrap">
        <div class="{{ $alignClass }} max-w-3xl">
            @if($block->get('title'))
                <h2 class="font-serif text-section-title {{ $titleClass }}">{{ $block->get('title') }}</h2>
            @endif
            @if($block->get('subtitle'))
                <p class="mt-4 {{ $subtitleClass }} leading-[1.65]">{{ $block->get('subtitle') }}</p>
            @endif
            @if($block->get('cta_label'))
                <a href="{{ $block->get('cta_url', '#') }}" class="{{ $buttonClass }} mt-7">{{ $block->get('cta_label') }}</a>
            @endif
        </div>
    </div>
</section>
