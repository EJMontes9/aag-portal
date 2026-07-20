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

{{-- Carrusel de banners -- Propuesta B.
     Solo cambia la apariencia: la maquinaria Alpine (autoplay, swipe, teclado,
     indicadores) es identica. En B no hay circulos: las flechas son cuadradas
     y los indicadores son barras rectangulares de 3px, no puntos. --}}
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
                {{-- .bg-cloud-gradient es el placeholder de imagen oficial de B
                     (navy->celeste); antes se mezclaba ademas el amarillo, que
                     en B esta reservado a acciones y filetes. --}}
                <div class="absolute inset-0 bg-cloud-gradient"></div>
            @endif

            {{-- Overlay oscuro --}}
            @if(($slide['overlay'] ?? 'medium') !== 'none')
                <div class="absolute inset-0 bg-brand-navy" style="opacity: {{ $overlayOpacity }};"></div>
            @endif

            {{-- Contenido --}}
            <div class="relative h-full flex flex-col justify-center {{ $alignClass }} max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14">
                <div class="max-w-3xl">
                    {{-- Filete amarillo de 3px: es el separador estructural de B y
                         aqui sustituye al kicker, que este bloque no tiene. --}}
                    <span class="block w-14 rule-accent mb-5"></span>
                    {{-- text-shadow (no box-shadow): la regla de "cero sombras" es
                         sobre cajas; aqui es legibilidad del texto blanco cuando el
                         editor elige overlay "none" sobre una foto clara. --}}
                    <h2 class="font-serif text-display text-white"
                        style="text-shadow: 0 2px 16px rgba(0,0,0,0.45);">
                        {{ $slide['title'] }}
                    </h2>
                    @if(!empty($slide['subtitle']))
                        <p class="mt-4 text-[15px] md:text-[17px] text-white/90 leading-[1.55] max-w-xl"
                           style="text-shadow: 0 1px 10px rgba(0,0,0,0.45);">
                            {{ $slide['subtitle'] }}
                        </p>
                    @endif
                    @if(!empty($slide['cta_label']) && !empty($slide['cta_url']))
                        {{-- Sobre fondo oscuro/foto la accion principal de B es el
                             amarillo institucional: eso es exactamente .btn-white. --}}
                        <a href="{{ $slide['cta_url'] }}" class="btn-white mt-6">
                            {{ $slide['cta_label'] }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    {{-- Flechas: cuadradas (radio 2px), sin blur ni circulo --}}
    @if($showArrows && $slides->count() > 1)
        <button type="button"
                @click="prev()"
                aria-label="Slide anterior"
                class="absolute left-4 md:left-6 top-1/2 -translate-y-1/2 z-20 w-10 h-10 md:w-11 md:h-11 rounded-pill bg-brand-navy/75 hover:bg-brand-primary border border-white/40 flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent focus-visible:ring-offset-2 focus-visible:ring-offset-brand-navy">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
        </button>
        <button type="button"
                @click="next()"
                aria-label="Slide siguiente"
                class="absolute right-4 md:right-6 top-1/2 -translate-y-1/2 z-20 w-10 h-10 md:w-11 md:h-11 rounded-pill bg-brand-navy/75 hover:bg-brand-primary border border-white/40 flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent focus-visible:ring-offset-2 focus-visible:ring-offset-brand-navy">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
            </svg>
        </button>
    @endif

    {{-- Indicadores: barras rectangulares. El activo va en amarillo de accion. --}}
    @if($showIndicators && $slides->count() > 1)
        <div class="absolute bottom-5 md:bottom-7 left-1/2 -translate-x-1/2 z-20 flex gap-1.5"
             role="tablist"
             aria-label="Selector de slide">
            @foreach($slides as $i => $_)
                {{-- El indicador visible sigue siendo la barra de 3px de B, pero el
                     area clicable se amplia con padding vertical: 3px de alto es un
                     objetivo tactil inaceptable en movil. --}}
                <button type="button"
                        @click="go({{ $i }})"
                        :aria-selected="current === {{ $i }}"
                        class="group py-2.5 px-0.5 rounded-pill focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent"
                        aria-label="Ir al slide {{ $i + 1 }}"
                        role="tab">
                    <span class="block h-[3px] transition-all duration-300"
                          :class="current === {{ $i }} ? 'w-10 bg-brand-accent' : 'w-5 bg-white/50 group-hover:bg-white/90'"></span>
                </button>
            @endforeach
        </div>
    @endif
</section>
