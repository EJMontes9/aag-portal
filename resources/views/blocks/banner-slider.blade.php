@props(['block'])
@php
    $slides = collect($block->get('slides', []))->filter(fn($s) => !empty($s['title']))->values();
    if ($slides->isEmpty()) return;

    $autoplay = (bool) $block->get('autoplay', true);
    $interval = max(2, (int) $block->get('interval', 6)) * 1000;
    $showIndicators = (bool) $block->get('show_indicators', true);
    $showArrows = (bool) $block->get('show_arrows', true);
    $height = $block->get('height', 'medium');

    $heightClass = match($height) {
        'small' => 'h-[50vh] min-h-[380px]',
        'large' => 'h-[85vh] min-h-[600px]',
        'full' => 'h-screen',
        default => 'h-[70vh] min-h-[500px]',
    };
@endphp

<section class="relative bg-brand-navy text-on-navy overflow-hidden {{ $heightClass }}"
         x-data="{
             current: 0,
             total: {{ $slides->count() }},
             autoplay: {{ $autoplay ? 'true' : 'false' }},
             interval: {{ $interval }},
             timer: null,
             touchStart: 0,
             touchEnd: 0,
             go(i) {
                 this.current = (i + this.total) % this.total;
                 this.restart();
             },
             next() { this.go(this.current + 1); },
             prev() { this.go(this.current - 1); },
             start() {
                 if (!this.autoplay || this.total < 2) return;
                 this.timer = setInterval(() => this.next(), this.interval);
             },
             stop() { clearInterval(this.timer); this.timer = null; },
             restart() { this.stop(); this.start(); },
             handleSwipe() {
                 const diff = this.touchStart - this.touchEnd;
                 if (Math.abs(diff) > 50) {
                     diff > 0 ? this.next() : this.prev();
                 }
             }
         }"
         x-init="start()"
         @mouseenter="stop()"
         @mouseleave="start()"
         @keydown.window.arrow-left="prev()"
         @keydown.window.arrow-right="next()"
         @touchstart="touchStart = $event.changedTouches[0].screenX"
         @touchend="touchEnd = $event.changedTouches[0].screenX; handleSwipe()"
         role="region"
         aria-roledescription="carousel"
         aria-label="Banner principal">

    {{-- Slides --}}
    @foreach($slides as $i => $slide)
        @php
            $img = !empty($slide['image']) ? \Illuminate\Support\Facades\Storage::disk('public')->url($slide['image']) : null;
            $overlayOpacity = match($slide['overlay'] ?? 'medium') {
                'none' => '0',
                'light' => '0.3',
                'strong' => '0.7',
                default => '0.5',
            };
            $alignClass = match($slide['align'] ?? 'left') {
                'center' => 'items-center text-center',
                'right' => 'items-end text-right',
                default => 'items-start text-left',
            };
        @endphp
        <div class="absolute inset-0 transition-opacity duration-700 ease-in-out"
             x-show="current === {{ $i }}"
             x-transition:enter="transition-opacity duration-700"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-700"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             role="group"
             aria-roledescription="slide"
             aria-label="Slide {{ $i + 1 }} de {{ $slides->count() }}"
             style="display: none;">

            {{-- Imagen de fondo o gradiente fallback --}}
            @if($img)
                <img src="{{ $img }}"
                     alt="{{ $slide['title'] ?? '' }}"
                     class="absolute inset-0 w-full h-full object-cover"
                     loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                     fetchpriority="{{ $i === 0 ? 'high' : 'auto' }}">
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-brand-navy via-brand-primary to-brand-accent"></div>
            @endif

            {{-- Overlay oscuro --}}
            @if(($slide['overlay'] ?? 'medium') !== 'none')
                <div class="absolute inset-0 bg-black" style="opacity: {{ $overlayOpacity }};"></div>
            @endif

            {{-- Contenido --}}
            <div class="relative h-full flex flex-col justify-center {{ $alignClass }} max-w-[1280px] mx-auto px-6 md:px-10">
                <div class="max-w-3xl">
                    <h2 class="font-serif text-4xl md:text-5xl lg:text-6xl leading-[1.1] text-white"
                        style="font-weight:500; text-shadow: 0 2px 24px rgba(0,0,0,0.4);">
                        {{ $slide['title'] }}
                    </h2>
                    @if(!empty($slide['subtitle']))
                        <p class="mt-6 text-lg md:text-xl text-white/90 leading-[1.55]"
                           style="text-shadow: 0 1px 12px rgba(0,0,0,0.4);">
                            {{ $slide['subtitle'] }}
                        </p>
                    @endif
                    @if(!empty($slide['cta_label']) && !empty($slide['cta_url']))
                        <a href="{{ $slide['cta_url'] }}"
                           class="inline-flex items-center gap-2 mt-8 px-7 py-3.5 bg-white text-brand-navy rounded-full font-semibold text-sm hover:bg-brand-soft transition-colors duration-200">
                            {{ $slide['cta_label'] }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    {{-- Flechas --}}
    @if($showArrows && $slides->count() > 1)
        <button type="button"
                @click="prev()"
                aria-label="Slide anterior"
                class="absolute left-4 md:left-6 top-1/2 -translate-y-1/2 z-20 w-11 h-11 md:w-12 md:h-12 rounded-full bg-white/15 hover:bg-white/30 backdrop-blur-sm border border-white/20 flex items-center justify-center transition-colors">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
        </button>
        <button type="button"
                @click="next()"
                aria-label="Slide siguiente"
                class="absolute right-4 md:right-6 top-1/2 -translate-y-1/2 z-20 w-11 h-11 md:w-12 md:h-12 rounded-full bg-white/15 hover:bg-white/30 backdrop-blur-sm border border-white/20 flex items-center justify-center transition-colors">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
            </svg>
        </button>
    @endif

    {{-- Indicadores --}}
    @if($showIndicators && $slides->count() > 1)
        <div class="absolute bottom-6 md:bottom-8 left-1/2 -translate-x-1/2 z-20 flex gap-2.5"
             role="tablist"
             aria-label="Selector de slide">
            @foreach($slides as $i => $_)
                <button type="button"
                        @click="go({{ $i }})"
                        :aria-selected="current === {{ $i }}"
                        :class="current === {{ $i }} ? 'w-8 bg-white' : 'w-2 bg-white/50 hover:bg-white/75'"
                        class="h-2 rounded-full transition-all duration-300"
                        aria-label="Ir al slide {{ $i + 1 }}"
                        role="tab"></button>
            @endforeach
        </div>
    @endif
</section>
