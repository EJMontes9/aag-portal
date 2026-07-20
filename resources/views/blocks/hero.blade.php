@props(['block'])
@php
    $layout    = $block->get('layout', 'editorial');
    $pill      = $block->get('pill');
    $pillTone  = $block->get('pill_tone', 'success');
    $h1        = $block->get('h1');
    $subtitle  = $block->get('subtitle');
    $stats     = $block->get('stats', []);

    // Los tonos del badge conservan sus CLAVES (estan guardadas en BD) pero se
    // reexpresan en el lenguaje de B: rectangulos de 2px sin borde, sobre los
    // colores institucionales. En la maqueta el badge del hero es amarillo.
    $pillColors = [
        'success' => 'bg-brand-accent text-on-accent',
        'warn'    => 'bg-brand-accent text-on-accent',
        'neutral' => 'bg-card text-brand-navy border border-border',
        'soft'    => 'bg-brand-soft text-brand-navy',
        'accent'  => 'bg-brand-accent text-on-accent',
    ];
    $pillClass = $pillColors[$pillTone] ?? $pillColors['accent'];

    // Rejilla de cifras: clases LITERALES. Tailwind escanea el codigo en build,
    // asi que "grid-cols-{$n}" construido en runtime nunca llega a compilarse
    // (era un bug real de esta plantilla, repetido en los cuatro layouts).
    $statCols = match(min(count($stats), 4)) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        3 => 'grid-cols-3',
        default => 'grid-cols-2 md:grid-cols-4',
    };
@endphp

@if($layout === 'editorial')
{{-- ══ EDITORIAL: texto izquierda + tarjetas derecha ══ --}}
@php
    $cards     = collect($block->get('cards', []));
    $imageCard = $cards->firstWhere('variant', 'image');
    $sideCards = $cards->where('variant', '!=', 'image')->values()->take(2);
@endphp
<section class="bg-bg">
    <div class="max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14 py-10 md:py-14 grid lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)] gap-8 lg:gap-12 items-start">

        {{-- Columna texto --}}
        <div>
            @if($pill)
                <span class="pill {{ $pillClass }} mb-5" data-aos="fade-down">
                    @if($pillTone === 'success')<span class="status-dot"></span>@endif
                    {{ $pill }}
                </span>
            @endif
            @if($h1)
                <h1 class="font-serif text-display text-brand-navy" data-hero-title>{!! italic_markdown_words($h1) !!}</h1>
            @endif
            @if($subtitle)
                <p class="mt-4 text-[15px] text-muted max-w-[560px] leading-relaxed" data-aos="fade-up" data-aos-delay="300">{{ $subtitle }}</p>
            @endif
            @if($block->get('cta1_label') || $block->get('cta2_label'))
                <div class="mt-6 flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="450">
                    @if($block->get('cta1_label'))<a href="{{ $block->get('cta1_url','#') }}" class="btn-primary">{{ $block->get('cta1_label') }}</a>@endif
                    @if($block->get('cta2_label'))<a href="{{ $block->get('cta2_url','#') }}" class="btn-ghost">{{ $block->get('cta2_label') }}</a>@endif
                </div>
            @endif
            @if(count($stats))
                <div class="mt-10 grid {{ $statCols }} gap-4 md:gap-6 max-w-[580px]" data-aos="fade-up" data-aos-delay="600">
                    @foreach($stats as $s)
                        @php $cc = parse_stat_value_for_animation($s['value'] ?? ''); @endphp
                        <div class="flex flex-col gap-1">
                            <span class="font-serif text-[30px] md:text-[36px] text-brand-navy leading-none num-tabular"
                                  @if($cc) data-count-to="{{ $cc['target'] }}" data-count-format="{{ $cc['format'] }}" @if(!empty($cc['suffix'])) data-count-suffix="{{ $cc['suffix'] }}" @endif @endif>{{ $s['value'] ?? '' }}</span>
                            <span class="font-sans text-[12px] tracking-[0.1em] uppercase text-muted font-bold leading-tight">{{ $s['label'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Columna tarjetas.
             Por debajo de sm la rejilla de 2 columnas dejaba tarjetas de ~160px
             de ancho con titulos de 17px: se apila en una sola columna y solo a
             partir de sm recupera el mosaico de la maqueta. --}}
        @if($cards->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 sm:grid-rows-[170px_170px] gap-4 w-full">
            @if($imageCard)
                <div class="sm:row-span-2 rounded-card overflow-hidden relative bg-cloud-gradient min-h-[220px] sm:min-h-[356px] flex flex-col justify-end"
                     data-aos="fade-left" data-aos-delay="200" data-aos-duration="700">
                    @if(!empty($imageCard['image']))
                        <img src="{{ Storage::disk('public')->url($imageCard['image']) }}" alt="{{ $imageCard['title'] ?? '' }}" class="absolute inset-0 w-full h-full object-cover">
                        {{-- Velo navy, no negro: el degradado a negro grisaceo era de la Propuesta A --}}
                        <div class="absolute inset-0" style="background:linear-gradient(to top, rgb(var(--color-navy) / 0.85), rgb(var(--color-navy) / 0.15) 60%, transparent);"></div>
                    @endif
                    <div class="relative p-5">
                        @if(!empty($imageCard['kicker']))<span class="pill bg-brand-accent text-on-accent mb-2">{{ $imageCard['kicker'] }}</span>@endif
                        @if(!empty($imageCard['title']))<h3 class="font-serif text-xl text-white leading-tight">{{ $imageCard['title'] }}</h3>@endif
                        @if(!empty($imageCard['cta_label']) && !empty($imageCard['cta_url']))
                            <a href="{{ $imageCard['cta_url'] }}" class="mt-2.5 inline-flex items-center gap-2 rounded-pill text-[12px] font-bold uppercase tracking-[0.06em] text-white hover:text-brand-accent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent">{{ $imageCard['cta_label'] }} →</a>
                        @endif
                    </div>
                </div>
            @endif
            @foreach($sideCards as $idx => $card)
                @php
                    $isPrimary   = ($card['variant'] ?? 'surface') === 'primary';
                    $cardClasses = $isPrimary ? 'bg-brand-navy text-on-navy rounded-card' : 'card-surface';
                    $kickerCls   = $isPrimary ? 'text-brand-accent' : 'text-brand-primary';
                    $titleColor  = $isPrimary ? 'text-on-navy' : 'text-brand-navy';
                    $borderColor = $isPrimary ? 'border-white/15' : 'border-border';
                    $metaColor   = $isPrimary ? 'text-on-navy/70' : 'text-muted';
                    $linkColor   = $isPrimary ? 'text-brand-accent' : 'text-brand-primary';
                @endphp
                <div class="{{ $cardClasses }} p-4 flex flex-col justify-between transition-colors duration-200 @if($imageCard) sm:col-start-2 @endif"
                     data-aos="fade-left" data-aos-delay="{{ 350 + $idx * 130 }}" data-aos-duration="600">
                    <div>
                        @if(!empty($card['kicker']))<span class="font-sans text-[12px] tracking-[0.1em] uppercase {{ $kickerCls }} font-bold">{{ $card['kicker'] }}</span>@endif
                        @if(!empty($card['title']))<h3 class="font-serif text-[17px] leading-tight mt-1.5 {{ $titleColor }}">{{ $card['title'] }}</h3>@endif
                    </div>
                    @if(!empty($card['meta']) || (!empty($card['cta_label']) && !empty($card['cta_url'])))
                        <div class="flex items-center justify-between gap-2 mt-3 @if(!empty($card['meta'])) pt-2.5 border-t {{ $borderColor }} @endif">
                            @if(!empty($card['meta']))<span class="text-[12px] {{ $metaColor }}">{{ $card['meta'] }}</span>@endif
                            @if(!empty($card['cta_label']) && !empty($card['cta_url']))
                                <a href="{{ $card['cta_url'] }}" class="shrink-0 rounded-pill text-[12px] font-bold uppercase tracking-[0.06em] hover:underline transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $linkColor }}">{{ $card['cta_label'] }} →</a>
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
    $bgClass   = match($bgColor) {
        'soft' => 'bg-brand-soft',
        'navy' => 'bg-brand-navy',
        'gradient' => 'bg-cloud-gradient',
        default => 'bg-bg',
    };
    $isDark    = in_array($bgColor, ['navy', 'gradient']);
    $centered  = $textAlign === 'center';
@endphp
<section class="{{ $bgClass }}">
    <div class="max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14 py-14 md:py-20 flex flex-col {{ $centered ? 'items-center text-center' : '' }}">
        <div class="{{ $centered ? 'max-w-4xl mx-auto' : 'max-w-3xl' }}">
            @if($pill)
                <span class="pill {{ $isDark ? 'bg-brand-accent text-on-accent' : $pillClass }} mb-5 inline-flex" data-aos="fade-down">
                    @if($pillTone === 'success' && !$isDark)<span class="status-dot"></span>@endif
                    {{ $pill }}
                </span>
            @endif
            @if($h1)
                <h1 class="font-serif text-display {{ $isDark ? 'text-white' : 'text-brand-navy' }}" data-hero-title>{!! italic_markdown_words($h1) !!}</h1>
            @endif
            @if($subtitle)
                <p class="mt-4 text-base {{ $isDark ? 'text-white/85' : 'text-muted' }} leading-relaxed">{{ $subtitle }}</p>
            @endif
            @if($block->get('cta1_label') || $block->get('cta2_label'))
                <div class="mt-7 flex flex-wrap gap-3 {{ $centered ? 'justify-center' : '' }}" data-aos="fade-up" data-aos-delay="450">
                    @if($block->get('cta1_label'))<a href="{{ $block->get('cta1_url','#') }}" class="{{ $isDark ? 'btn-white' : 'btn-primary' }}">{{ $block->get('cta1_label') }}</a>@endif
                    @if($block->get('cta2_label'))<a href="{{ $block->get('cta2_url','#') }}" class="{{ $isDark ? 'btn-ghost-white' : 'btn-ghost' }}">{{ $block->get('cta2_label') }}</a>@endif
                </div>
            @endif
        </div>
        @if(count($stats))
            <div class="mt-12 grid {{ $statCols }} gap-6" data-aos="fade-up" data-aos-delay="600">
                @foreach($stats as $s)
                    @php $cc = parse_stat_value_for_animation($s['value'] ?? ''); @endphp
                    <div class="flex flex-col gap-1 {{ $centered ? 'items-center' : '' }}">
                        <span class="font-serif text-[34px] {{ $isDark ? 'text-white' : 'text-brand-navy' }} leading-none num-tabular"
                              @if($cc) data-count-to="{{ $cc['target'] }}" data-count-format="{{ $cc['format'] }}" @endif>{{ $s['value'] ?? '' }}</span>
                        <span class="font-sans text-[12px] tracking-[0.1em] uppercase {{ $isDark ? 'text-white/70' : 'text-muted' }} font-bold">{{ $s['label'] ?? '' }}</span>
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
    <div class="max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14 py-10 md:py-14 grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
        <div>
            @if($pill)
                <span class="pill {{ $pillClass }} mb-5" data-aos="fade-down">
                    @if($pillTone === 'success')<span class="status-dot"></span>@endif {{ $pill }}
                </span>
            @endif
            @if($h1)<h1 class="font-serif text-display text-brand-navy" data-hero-title>{!! italic_markdown_words($h1) !!}</h1>@endif
            @if($subtitle)<p class="mt-4 text-[15px] text-muted max-w-[560px] leading-relaxed" data-aos="fade-up" data-aos-delay="300">{{ $subtitle }}</p>@endif
            @if($block->get('cta1_label') || $block->get('cta2_label'))
                <div class="mt-6 flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="450">
                    @if($block->get('cta1_label'))<a href="{{ $block->get('cta1_url','#') }}" class="btn-primary">{{ $block->get('cta1_label') }}</a>@endif
                    @if($block->get('cta2_label'))<a href="{{ $block->get('cta2_url','#') }}" class="btn-ghost">{{ $block->get('cta2_label') }}</a>@endif
                </div>
            @endif
            @if(count($stats))
                <div class="mt-10 grid {{ $statCols }} gap-4 md:gap-6 max-w-[580px]" data-aos="fade-up" data-aos-delay="600">
                    @foreach($stats as $s)
                        @php $cc = parse_stat_value_for_animation($s['value'] ?? ''); @endphp
                        <div class="flex flex-col gap-1">
                            <span class="font-serif text-[30px] md:text-[36px] text-brand-navy leading-none num-tabular"
                                  @if($cc) data-count-to="{{ $cc['target'] }}" data-count-format="{{ $cc['format'] }}" @endif>{{ $s['value'] ?? '' }}</span>
                            <span class="font-sans text-[12px] tracking-[0.1em] uppercase text-muted font-bold leading-tight">{{ $s['label'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="relative rounded-card overflow-hidden aspect-[4/3] lg:aspect-auto lg:min-h-[380px] bg-cloud-gradient"
             data-aos="fade-left" data-aos-delay="200" data-aos-duration="700">
            @if($sideImgUrl)
                <img src="{{ $sideImgUrl }}" alt="{{ strip_tags($h1 ?? '') }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full min-h-[320px] flex items-center justify-center text-white/40">
                    <svg class="w-20 h-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
        </div>
    </div>
</section>

@elseif($layout === 'banner')
{{-- ══ BANNER: fondo de imagen a todo el ancho ══
     En la maqueta B el hero es una FRANJA BAJA (260px) con overlay navy fuerte
     y contenido centrado, no el bloque editorial de ~500px de la Propuesta A. --}}
@php
    $bgImg    = $block->get('background_image');
    $bgImgUrl = $bgImg ? Storage::disk('public')->url($bgImg) : null;
    $overlay  = $block->get('bg_overlay', 'medium');
    $textAlign = $block->get('text_align', 'center');
    $overlayOpacity = match($overlay) { 'light' => '0.45', 'dark' => '0.85', default => '0.72' };
    $centered = $textAlign === 'center';
@endphp
<section class="relative min-h-[260px] md:min-h-[300px] flex items-center overflow-hidden bg-brand-navy">
    @if($bgImgUrl)
        <img src="{{ $bgImgUrl }}" alt="" class="absolute inset-0 w-full h-full object-cover" aria-hidden="true">
    @endif
    <div class="absolute inset-0 bg-brand-navy" style="opacity:{{ $overlayOpacity }}"></div>
    <div class="relative z-10 w-full max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14 py-10 flex flex-col {{ $centered ? 'items-center text-center' : '' }}">
        <div class="{{ $centered ? 'max-w-3xl mx-auto' : 'max-w-2xl' }}">
            @if($pill)
                <span class="pill {{ $pillClass }} mb-3.5 inline-flex" data-aos="fade-down">{{ $pill }}</span>
            @endif
            @if($h1)
                <h1 class="font-serif text-display text-white" data-hero-title>{!! italic_markdown_words($h1) !!}</h1>
            @endif
            @if($subtitle)
                <p class="mt-3.5 text-base text-white/85 leading-relaxed {{ $centered ? 'max-w-[720px] mx-auto' : '' }}" data-aos="fade-up" data-aos-delay="300">{{ $subtitle }}</p>
            @endif
            @if($block->get('cta1_label') || $block->get('cta2_label'))
                <div class="mt-6 flex flex-wrap gap-2.5 {{ $centered ? 'justify-center' : '' }}" data-aos="fade-up" data-aos-delay="450">
                    @if($block->get('cta1_label'))<a href="{{ $block->get('cta1_url','#') }}" class="btn-white">{{ $block->get('cta1_label') }}</a>@endif
                    @if($block->get('cta2_label'))<a href="{{ $block->get('cta2_url','#') }}" class="btn-ghost-white">{{ $block->get('cta2_label') }}</a>@endif
                </div>
            @endif
        </div>
        @if(count($stats))
            <div class="mt-10 grid {{ $statCols }} gap-6 {{ $centered ? 'text-center' : '' }}" data-aos="fade-up" data-aos-delay="600">
                @foreach($stats as $s)
                    @php $cc = parse_stat_value_for_animation($s['value'] ?? ''); @endphp
                    <div class="flex flex-col gap-1 {{ $centered ? 'items-center' : '' }}">
                        <span class="font-serif text-[34px] text-white leading-none num-tabular"
                              @if($cc) data-count-to="{{ $cc['target'] }}" data-count-format="{{ $cc['format'] }}" @endif>{{ $s['value'] ?? '' }}</span>
                        <span class="font-sans text-[12px] tracking-[0.1em] uppercase text-white/70 font-bold">{{ $s['label'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif
