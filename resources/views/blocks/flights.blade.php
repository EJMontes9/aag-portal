@props(['block'])

<section id="vuelos" class="bg-brand-navy text-on-navy relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(91,143,217,0.25),transparent_60%)]"></div>
    <div class="relative max-w-[1280px] mx-auto px-6 md:px-10 py-20 md:py-28 grid lg:grid-cols-[1fr_1.15fr] gap-12 items-center">
        <div data-aos="fade-right" data-aos-duration="700">
            @if($block->get('kicker'))
                <span class="font-sans text-[11px] tracking-[0.16em] uppercase text-on-navy/60 font-semibold">{{ $block->get('kicker') }}</span>
            @endif
            @if($block->get('title'))
                <h2 class="font-serif text-section-title text-on-navy mt-3">{{ $block->get('title') }}</h2>
            @endif
            @if($block->get('subtitle'))
                <p class="mt-5 text-on-navy/75 max-w-lg leading-[1.65]">{{ $block->get('subtitle') }}</p>
            @endif
            @if($block->get('cta_label'))
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ $block->get('cta_url', '#') }}" target="_blank" rel="noopener" class="btn-primary">{{ $block->get('cta_label') }}</a>
                    @if($block->get('cta_note'))
                        <span class="text-xs text-on-navy/50">{{ $block->get('cta_note') }}</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="bg-white/[0.06] backdrop-blur rounded-2xl border border-white/10 p-5 shadow-xl"
             data-aos="fade-left" data-aos-duration="700" data-aos-delay="150">
            <div class="flex items-center justify-between mb-4 px-2">
                <div class="flex gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-400/70"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400/70"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400/70"></span>
                </div>
                <span class="text-[11px] text-on-navy/40 font-mono">tagsa.aero/vuelos</span>
            </div>
            <div class="bg-white/5 rounded-xl divide-y divide-white/10">
                @php
                    $dotStyles = [
                        'emerald' => 'w-2 h-2 rounded-full bg-emerald-400',
                        'amber' => 'w-2 h-2 rounded-full bg-amber-400 animate-blink',
                        'rose' => 'w-2 h-2 rounded-full bg-rose-400 animate-blink',
                    ];
                @endphp
                @foreach([
                    ['14:20', 'Bogota', 'A tiempo', 'emerald'],
                    ['14:45', 'Quito', 'Abordando', 'amber'],
                    ['15:10', 'Panama', 'A tiempo', 'emerald'],
                    ['15:35', 'Madrid', 'Retrasado', 'rose'],
                ] as [$hora, $destino, $estado, $tono])
                    <div class="flex items-center justify-between py-3.5 px-5 transition-colors duration-200 hover:bg-white/5"
                         data-stagger="flight-row" style="opacity:0;">
                        <div class="flex items-center gap-8">
                            <span class="font-mono num-tabular text-on-navy text-sm">{{ $hora }}</span>
                            <span class="text-sm text-on-navy/90">{{ $destino }}</span>
                        </div>
                        <span class="flex items-center gap-2.5 text-xs text-on-navy/80">
                            <span class="{{ $dotStyles[$tono] }}"></span>
                            {{ $estado }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
