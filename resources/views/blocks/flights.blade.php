@props(['block'])

{{-- Vuelos -- Propuesta B.
     Antes el panel usaba color fuera del sistema (bg-white/[0.06], rounded-2xl,
     shadow-xl, un radial-gradient con un azul literal y tres puntos rojo/ambar/
     verde imitando el "chrome" de un navegador). Ahora es una banda navy plana
     con un tablero de salidas resuelto como caja blanca con borde, que es EL
     mecanismo de separacion visual de B. --}}
<section id="vuelos" class="bg-brand-navy text-on-navy">
    <div class="section-wrap grid lg:grid-cols-[1fr_1.15fr] gap-8 lg:gap-14 items-center">
        <div data-aos="fade-right" data-aos-duration="700">
            @if($block->get('kicker'))
                {{-- El kicker es celeste por defecto; sobre navy se sube a amarillo
                     para que despegue del fondo. --}}
                <span class="kicker text-brand-accent">{{ $block->get('kicker') }}</span>
            @endif
            @if($block->get('title'))
                <h2 class="font-serif text-section-title text-on-navy mt-2">{{ $block->get('title') }}</h2>
            @endif
            @if($block->get('subtitle'))
                <p class="mt-4 text-sm text-on-navy/75 max-w-[460px] leading-[1.6]">{{ $block->get('subtitle') }}</p>
            @endif
            @if($block->get('cta_label'))
                <div class="mt-6 flex flex-wrap items-center gap-4">
                    {{-- Sobre fondo oscuro la accion principal de B es el amarillo
                         (.btn-white), no el navy de .btn-primary, que se perderia. --}}
                    <a href="{{ $block->get('cta_url', '#') }}" target="_blank" rel="noopener" class="btn-white">{{ $block->get('cta_label') }}</a>
                    @if($block->get('cta_note'))
                        <span class="text-xs text-on-navy/60">{{ $block->get('cta_note') }}</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="card-surface overflow-hidden"
             data-aos="fade-left" data-aos-duration="700" data-aos-delay="150">
            {{-- Cabecera del tablero: banda navy rematada por el filete amarillo
                 de 3px, el mismo recurso que separa la marca de la nav. --}}
            <div class="flex items-center justify-between gap-3 bg-brand-navy rule-accent px-4 py-2.5">
                <span class="font-sans text-[11px] font-bold uppercase tracking-[0.14em] text-on-navy">Proximas salidas</span>
                <span class="font-mono text-[11px] text-on-navy/60">tagsa.aero/vuelos</span>
            </div>
            <div class="divide-y divide-border">
                @php
                    // Las claves de tono se conservan; solo cambian las clases.
                    // .status-dot* son los indicadores del sistema de diseno.
                    $dotStyles = [
                        'emerald' => 'status-dot',
                        'amber' => 'status-dot-amber',
                        'rose' => 'status-dot-red',
                    ];
                @endphp
                @foreach([
                    ['14:20', 'Bogota', 'A tiempo', 'emerald'],
                    ['14:45', 'Quito', 'Abordando', 'amber'],
                    ['15:10', 'Panama', 'A tiempo', 'emerald'],
                    ['15:35', 'Madrid', 'Retrasado', 'rose'],
                ] as [$hora, $destino, $estado, $tono])
                    <div class="flex items-center justify-between gap-4 py-3 px-4 transition-colors duration-200 hover:bg-brand-soft/40"
                         data-stagger="flight-row" style="opacity:0;">
                        <div class="flex items-center gap-6">
                            <span class="font-mono num-tabular text-[13px] font-bold text-brand-navy">{{ $hora }}</span>
                            <span class="font-sans text-[13px] text-fg">{{ $destino }}</span>
                        </div>
                        <span class="flex items-center gap-2 font-sans text-[11px] font-bold uppercase tracking-[0.06em] text-muted">
                            <span class="{{ $dotStyles[$tono] }}"></span>
                            {{ $estado }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
