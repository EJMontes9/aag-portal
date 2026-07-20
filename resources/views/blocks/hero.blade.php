@props(['block'])
@php
    $layout    = $block->get('layout', 'editorial');
    $pill      = $block->get('pill');
    $pillTone  = $block->get('pill_tone', 'success');
    $h1        = $block->get('h1');
    $subtitle  = $block->get('subtitle');
    $stats     = $block->get('stats', []);

    $pillColors = [
        'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'warn'    => 'bg-amber-50 text-amber-800 border border-amber-200',
        'neutral' => 'bg-card text-fg border border-border',
        'soft'    => 'bg-brand-soft/70 text-brand-navy',
        'accent'  => 'bg-brand-accent text-on-accent',
    ];
    $pillClass = $pillColors[$pillTone] ?? $pillColors['success'];

@endphp

@if($layout === 'editorial')
{{-- ══ EDITORIAL: texto izquierda + tarjetas derecha ══ --}}
@php
    $cards     = collect($block->get('cards', []));
    $imageCard = $cards->firstWhere('variant', 'image');
    $sideCards = $cards->where('variant', '!=', 'image')->values()->take(2);
@endphp
<section class="bg-bg">
    <div class="max-w-[1280px] mx-auto px-6 md:px-10 pt-14 pb-20 md:pt-20 md:pb-28 grid lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)] gap-10 lg:gap-14 items-start">

        {{-- Columna texto --}}
        <div>
            @if($pill)
                <span class="pill {{ $pillClass }} mb-7" data-aos="fade-down">
                    @if($pillTone === 'success')<span class="status-dot"></span>@endif
                    {{ $pill }}
                </span>
            @endif
            @if($h1)
                <h1 class="font-serif text-display text-fg" style="font-weight:400;" data-hero-title>{!! italic_markdown_words($h1) !!}</h1>
            @endif
            @if($subtitle)
                <p class="mt-7 text-base text-muted max-w-[520px] leading-[1.65]" data-aos="fade-up" data-aos-delay="300">{{ $subtitle }}</p>
            @endif
            @if($block->get('cta1_label') || $block->get('cta2_label'))
                <div class="mt-9 flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="450">
                    @if($block->get('cta1_label'))<a href="{{ $block->get('cta1_url','#') }}" class="btn-primary">{{ $block->get('cta1_label') }}</a>@endif
                    @if($block->get('cta2_label'))<a href="{{ $block->get('cta2_url','#') }}" class="btn-ghost">{{ $block->get('cta2_label') }}</a>@endif
                </div>
            @endif
            @if(count($stats))
                <div class="mt-14 grid grid-cols-{{ min(count($stats),4) }} gap-4 md:gap-8 max-w-[580px]" data-aos="fade-up" data-aos-delay="600">
                    @foreach($stats as $s)
                        @php $cc = parse_stat_value_for_animation($s['value'] ?? ''); @endphp
                        <div class="flex flex-col gap-1.5">
                            <span class="font-serif text-[38px] md:text-[44px] font-normal text-fg leading-none tracking-[-0.02em]"
                                  @if($cc) data-count-to="{{ $cc['target'] }}" data-count-format="{{ $cc['format'] }}" @if(!empty($cc['suffix'])) data-count-suffix="{{ $cc['suffix'] }}" @endif @endif>{{ $s['value'] ?? '' }}</span>
                            <span class="font-sans text-[10px] tracking-[0.18em] uppercase text-muted font-semibold leading-tight">{{ $s['label'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Columna tarjetas --}}
        @if($cards->isNotEmpty())
        <div class="grid grid-cols-2 grid-rows-[180px_180px] gap-4 w-full">
            @if($imageCard)
                <div class="row-span-2 rounded-hero overflow-hidden relative bg-cloud-gradient min-h-[380px] flex flex-col justify-end transition-transform duration-500 hover:-translate-y-1 hover:shadow-2xl"
                     data-aos="fade-left" data-aos-delay="200" data-aos-duration="700">
                    @if(!empty($imageCard['image']))
                        <img src="{{ Storage::disk('public')->url($imageCard['image']) }}" alt="{{ $imageCard['title'] ?? '' }}" class="absolute inset-0 w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    @else
                        <svg class="absolute inset-0 w-full h-full opacity-90" viewBox="0 0 400 600" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                            <defs><linearGradient id="sky-{{ $block->id }}" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#c7dcf1"/><stop offset="60%" stop-color="#8ab4dc"/><stop offset="100%" stop-color="#3b5f8f"/>
                            </linearGradient></defs>
                            <rect width="400" height="600" fill="url(#sky-{{ $block->id }})"/>
                            <path d="M-30 540 Q 150 500,350 560 L 430 600 L -50 600 Z" fill="#1a3a6b" opacity="0.85"/>
                            <path d="M-30 560 Q 200 520,430 580 L 430 600 L -30 600 Z" fill="#0b1e4a" opacity="0.9"/>
                        </svg>
                    @endif
                    <div class="relative p-6">
                        @if(!empty($imageCard['kicker']))<span class="pill bg-white/90 text-brand-navy mb-3">{{ $imageCard['kicker'] }}</span>@endif
                        @if(!empty($imageCard['title']))<h3 class="font-serif text-2xl text-white leading-tight" style="text-shadow:0 2px 8px rgba(0,0,0,.25);">{{ $imageCard['title'] }}</h3>@endif
                        @if(!empty($imageCard['cta_label']) && !empty($imageCard['cta_url']))
                            <a href="{{ $imageCard['cta_url'] }}" class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-white hover:underline">{{ $imageCard['cta_label'] }} →</a>
                        @endif
                    </div>
                </div>
            @endif
            @foreach($sideCards as $idx => $card)
                @php
                    $isPrimary   = ($card['variant'] ?? 'surface') === 'primary';
                    $cardClasses = $isPrimary ? 'bg-brand-primary text-on-primary' : 'bg-card border border-border';
                    $kickerCls   = $isPrimary ? 'text-on-primary/70' : 'text-muted';
                    $titleColor  = $isPrimary ? 'text-on-primary' : 'text-fg';
                    $borderColor = $isPrimary ? 'border-on-primary/15' : 'border-border';
                    $metaColor   = $isPrimary ? 'text-on-primary/80' : 'text-muted';
                    $linkColor   = $isPrimary ? 'text-on-primary' : 'text-brand-primary';
                @endphp
                <div class="rounded-hero {{ $cardClasses }} p-5 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1 hover:shadow-xl @if($imageCard) col-start-2 @endif"
                     data-aos="fade-left" data-aos-delay="{{ 350 + $idx * 130 }}" data-aos-duration="600">
                    <div>
                        @if(!empty($card['kicker']))<span class="font-sans text-[10px] tracking-[0.16em] uppercase {{ $kickerCls }} font-semibold">{{ $card['kicker'] }}</span>@endif
                        @if(!empty($card['title']))<h3 class="font-serif text-[19px] leading-[1.2] mt-2 {{ $titleColor }}">{{ $card['title'] }}</h3>@endif
                    </div>
                    @if(!empty($card['meta']) || (!empty($card['cta_label']) && !empty($card['cta_url'])))
                        <div class="flex items-center justify-between mt-4 @if(!empty($card['meta'])) pt-3 border-t {{ $borderColor }} @endif">
                            @if(!empty($card['meta']))<span class="text-xs {{ $metaColor }}">{{ $card['meta'] }}</span>@endif
                            @if(!empty($card['cta_label']) && !empty($card['cta_url']))
                                <a href="{{ $card['cta_url'] }}" class="text-xs font-semibold hover:underline {{ $linkColor }}">{{ $card['cta_label'] }} →</a>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @endif

    </div>
</section>

@elseif($layout === 'centered')
{{-- ══ CENTRADO: texto grande, sin tarjetas ══ --}}
@php
    $bgColor   = $block->get('bg_color', 'light');
    $textAlign = $block->get('text_align', 'center');
    $bgClass   = match($bgColor) { 'soft' => 'bg-brand-soft/30', 'navy' => 'bg-brand-navy', 'gradient' => 'bg-gradient-to-br from-brand-primary to-brand-navy', default => 'bg-bg' };
    $isDark    = in_array($bgColor, ['navy', 'gradient']);
    $centered  = $textAlign === 'center';
@endphp
<section class="{{ $bgClass }}">
    <div class="max-w-[1280px] mx-auto px-6 md:px-10 py-24 md:py-36 flex flex-col {{ $centered ? 'items-center text-center' : '' }}">
        <div class="{{ $centered ? 'max-w-4xl mx-auto' : 'max-w-3xl' }}">
            @if($pill)
                <span class="pill {{ $isDark ? 'bg-white/15 text-white border border-white/20' : $pillClass }} mb-7 inline-flex" data-aos="fade-down">
                    @if($pillTone === 'success' && !$isDark)<span class="status-dot"></span>@endif
                    {{ $pill }}
                </span>
            @endif
            @if($h1)
                <h1 class="font-serif text-display {{ $isDark ? 'text-white' : 'text-fg' }}" style="font-weight:400;" data-hero-title>{!! italic_markdown_words($h1) !!}</h1>
            @endif
            @if($subtitle)
                <p class="mt-7 text-lg {{ $isDark ? 'text-white/75' : 'text-muted' }} leading-[1.65]" data-aos="fade-up" data-aos-delay="300">{{ $subtitle }}</p>
            @endif
            @if($block->get('cta1_label') || $block->get('cta2_label'))
                <div class="mt-10 flex flex-wrap gap-3 {{ $centered ? 'justify-center' : '' }}" data-aos="fade-up" data-aos-delay="450">
                    @if($block->get('cta1_label'))<a href="{{ $block->get('cta1_url','#') }}" class="{{ $isDark ? 'btn-white' : 'btn-primary' }}">{{ $block->get('cta1_label') }}</a>@endif
                    @if($block->get('cta2_label'))<a href="{{ $block->get('cta2_url','#') }}" class="{{ $isDark ? 'btn-ghost-white' : 'btn-ghost' }}">{{ $block->get('cta2_label') }}</a>@endif
                </div>
            @endif
        </div>
        @if(count($stats))
            <div class="mt-16 grid grid-cols-{{ min(count($stats),4) }} gap-8" data-aos="fade-up" data-aos-delay="600">
                @foreach($stats as $s)
                    @php $cc = parse_stat_value_for_animation($s['value'] ?? ''); @endphp
                    <div class="flex flex-col gap-1.5 {{ $centered ? 'items-center' : '' }}">
                        <span class="font-serif text-[42px] font-normal {{ $isDark ? 'text-white' : 'text-fg' }} leading-none tracking-[-0.02em]"
                              @if($cc) data-count-to="{{ $cc['target'] }}" data-count-format="{{ $cc['format'] }}" @endif>{{ $s['value'] ?? '' }}</span>
                        <span class="font-sans text-[10px] tracking-[0.18em] uppercase {{ $isDark ? 'text-white/60' : 'text-muted' }} font-semibold">{{ $s['label'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

@elseif($layout === 'split')
{{-- ══ SPLIT: texto izquierda + imagen derecha ══ --}}
@php
    $sideImg    = $block->get('side_image');
    $sideImgUrl = $sideImg ? Storage::disk('public')->url($sideImg) : null;
@endphp
<section class="bg-bg">
    <div class="max-w-[1280px] mx-auto px-6 md:px-10 pt-14 pb-20 md:pt-20 md:pb-28 grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
        <div>
            @if($pill)
                <span class="pill {{ $pillClass }} mb-7" data-aos="fade-down">
                    @if($pillTone === 'success')<span class="status-dot"></span>@endif {{ $pill }}
                </span>
            @endif
            @if($h1)<h1 class="font-serif text-display text-fg" style="font-weight:400;" data-hero-title>{!! italic_markdown_words($h1) !!}</h1>@endif
            @if($subtitle)<p class="mt-7 text-base text-muted max-w-[520px] leading-[1.65]" data-aos="fade-up" data-aos-delay="300">{{ $subtitle }}</p>@endif
            @if($block->get('cta1_label') || $block->get('cta2_label'))
                <div class="mt-9 flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="450">
                    @if($block->get('cta1_label'))<a href="{{ $block->get('cta1_url','#') }}" class="btn-primary">{{ $block->get('cta1_label') }}</a>@endif
                    @if($block->get('cta2_label'))<a href="{{ $block->get('cta2_url','#') }}" class="btn-ghost">{{ $block->get('cta2_label') }}</a>@endif
                </div>
            @endif
            @if(count($stats))
                <div class="mt-14 grid grid-cols-{{ min(count($stats),4) }} gap-4 md:gap-8 max-w-[580px]" data-aos="fade-up" data-aos-delay="600">
                    @foreach($stats as $s)
                        @php $cc = parse_stat_value_for_animation($s['value'] ?? ''); @endphp
                        <div class="flex flex-col gap-1.5">
                            <span class="font-serif text-[38px] md:text-[44px] font-normal text-fg leading-none tracking-[-0.02em]"
                                  @if($cc) data-count-to="{{ $cc['target'] }}" data-count-format="{{ $cc['format'] }}" @endif>{{ $s['value'] ?? '' }}</span>
                            <span class="font-sans text-[10px] tracking-[0.18em] uppercase text-muted font-semibold leading-tight">{{ $s['label'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="relative rounded-hero overflow-hidden aspect-[4/3] lg:aspect-auto lg:min-h-[480px]"
             data-aos="fade-left" data-aos-delay="200" data-aos-duration="700">
            @if($sideImgUrl)
                <img src="{{ $sideImgUrl }}" alt="{{ strip_tags($h1 ?? '') }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full min-h-[360px] bg-gradient-to-br from-brand-soft to-brand-accent/40 flex items-center justify-center">
                    <svg class="w-24 h-24 text-brand-accent/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
        </div>
    </div>
</section>

@elseif($layout === 'banner')
{{-- ══ BANNER: fondo de imagen full width ══ --}}
@php
    $bgImg    = $block->get('background_image');
    $bgImgUrl = $bgImg ? Storage::disk('public')->url($bgImg) : null;
    $overlay  = $block->get('bg_overlay', 'medium');
    $textAlign = $block->get('text_align', 'center');
    $overlayOpacity = match($overlay) { 'light' => '0.25', 'dark' => '0.70', default => '0.50' };
    $centered = $textAlign === 'center';
@endphp
<section class="relative min-h-[520px] md:min-h-[640px] flex items-center overflow-hidden bg-brand-navy">
    @if($bgImgUrl)
        <img src="{{ $bgImgUrl }}" alt="" class="absolute inset-0 w-full h-full object-cover" aria-hidden="true">
    @endif
    <div class="absolute inset-0 bg-brand-navy" style="opacity:{{ $overlayOpacity }}"></div>
    <div class="relative z-10 w-full max-w-[1280px] mx-auto px-6 md:px-10 py-24 flex flex-col {{ $centered ? 'items-center text-center' : '' }}">
        <div class="{{ $centered ? 'max-w-3xl mx-auto' : 'max-w-2xl' }}">
            @if($pill)
                <span class="pill {{ $pillClass }} mb-7 inline-flex" data-aos="fade-down">{{ $pill }}</span>
            @endif
            @if($h1)
                <h1 class="font-serif text-display text-white" style="font-weight:400; text-shadow:0 2px 20px rgba(0,0,0,.3);" data-hero-title>{!! italic_markdown_words($h1) !!}</h1>
            @endif
            @if($subtitle)
                <p class="mt-6 text-lg text-white/80 leading-[1.65]" data-aos="fade-up" data-aos-delay="300">{{ $subtitle }}</p>
            @endif
            @if($block->get('cta1_label') || $block->get('cta2_label'))
                <div class="mt-10 flex flex-wrap gap-3 {{ $centered ? 'justify-center' : '' }}" data-aos="fade-up" data-aos-delay="450">
                    @if($block->get('cta1_label'))<a href="{{ $block->get('cta1_url','#') }}" class="btn-white">{{ $block->get('cta1_label') }}</a>@endif
                    @if($block->get('cta2_label'))<a href="{{ $block->get('cta2_url','#') }}" class="btn-ghost-white">{{ $block->get('cta2_label') }}</a>@endif
                </div>
            @endif
        </div>
        @if(count($stats))
            <div class="mt-16 grid grid-cols-{{ min(count($stats),4) }} gap-8 {{ $centered ? 'text-center' : '' }}" data-aos="fade-up" data-aos-delay="600">
                @foreach($stats as $s)
                    @php $cc = parse_stat_value_for_animation($s['value'] ?? ''); @endphp
                    <div class="flex flex-col gap-1.5 {{ $centered ? 'items-center' : '' }}">
                        <span class="font-serif text-[42px] font-normal text-white leading-none tracking-[-0.02em]"
                              @if($cc) data-count-to="{{ $cc['target'] }}" data-count-format="{{ $cc['format'] }}" @endif>{{ $s['value'] ?? '' }}</span>
                        <span class="font-sans text-[10px] tracking-[0.18em] uppercase text-white/60 font-semibold">{{ $s['label'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif
