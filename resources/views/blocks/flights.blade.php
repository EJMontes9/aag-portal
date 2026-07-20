@props(['block'])

{{-- Estado de vuelos — Propuesta B.

     El tablero es una MUESTRA ILUSTRATIVA, no datos en vivo: el bloque no
     tiene campo para los vuelos y la AAG no expone un servicio propio; la
     informacion real esta en el portal de TAGSA, al que apunta el boton.
     Por eso lleva una marca visible de "ejemplo": un tablero de aeropuerto
     con aspecto de dato real induce a error al ciudadano, que puede llegar a
     planificar con el.

     Las horas se calculan a partir del momento de la visita en lugar de estar
     fijas: un panel congelado en "14:20" a las nueve de la manana se lee de
     inmediato como roto. --}}
@php
    $ahora = now();

    // Salidas de ejemplo: se reparten en tramos de 25 minutos desde la proxima
    // media hora en punto, para que el tablero sea siempre coherente con el
    // reloj del visitante.
    $base = $ahora->copy()->addMinutes(30 - ($ahora->minute % 30))->second(0);

    $salidas = [
        ['destino' => 'Quito',        'iata' => 'UIO', 'estado' => 'Abordando', 'tono' => 'proceso'],
        ['destino' => 'Bogotá',       'iata' => 'BOG', 'estado' => 'A tiempo',  'tono' => 'abierto'],
        ['destino' => 'Ciudad de Panamá', 'iata' => 'PTY', 'estado' => 'A tiempo', 'tono' => 'abierto'],
        ['destino' => 'Madrid',       'iata' => 'MAD', 'estado' => 'Retrasado', 'tono' => 'cerrado'],
    ];

    foreach ($salidas as $i => $s) {
        $salidas[$i]['hora'] = $base->copy()->addMinutes($i * 25)->format('H:i');
    }

    // Los chips de estado del sistema de diseno, en vez de puntos de color
    // sueltos: el mismo lenguaje que usan convocatorias y proyectos.
    $chips = [
        'abierto' => 'chip-abierto',
        'proceso' => 'chip-proceso',
        'cerrado' => 'chip-cerrado',
    ];
@endphp

<section id="vuelos" class="bg-brand-navy text-on-navy">
    <div class="section-wrap grid lg:grid-cols-[1fr_1.2fr] gap-8 lg:gap-14 items-center">

        {{-- ── Columna de texto ──────────────────────────────────────────── --}}
        <div data-aos="fade-right" data-aos-duration="700">
            @if($block->get('kicker'))
                {{-- El kicker es celeste por defecto; sobre navy sube a amarillo
                     para despegar del fondo. --}}
                <span class="kicker text-brand-accent">{{ $block->get('kicker') }}</span>
            @endif
            @if($block->get('title'))
                <h2 class="font-serif text-section-title text-on-navy mt-2">{{ $block->get('title') }}</h2>
            @endif
            @if($block->get('subtitle'))
                <p class="mt-4 text-[15px] text-on-navy/80 max-w-[460px] leading-relaxed">{{ $block->get('subtitle') }}</p>
            @endif
            @if($block->get('cta_label'))
                <div class="mt-7 flex flex-wrap items-center gap-4">
                    {{-- Sobre fondo oscuro la accion principal de B es el amarillo. --}}
                    <a href="{{ $block->get('cta_url', '#') }}" target="_blank" rel="noopener"
                       class="btn-white">
                        {{ $block->get('cta_label') }}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                    </a>
                    @if($block->get('cta_note'))
                        <span class="text-[13px] text-on-navy/70 max-w-[24ch]">{{ $block->get('cta_note') }}</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- ── Tablero ───────────────────────────────────────────────────── --}}
        <div class="card-surface overflow-hidden" data-aos="fade-left" data-aos-duration="700" data-aos-delay="150">

            {{-- Cabecera navy rematada por el filete amarillo --}}
            <div class="bg-brand-navy rule-accent px-5 py-3.5 flex items-center justify-between gap-3">
                <span class="font-sans text-[13px] font-bold uppercase tracking-[0.12em] text-on-navy">
                    Próximas salidas
                </span>
                {{-- Marca honesta: esto no son datos en vivo. --}}
                <span class="shrink-0 rounded-pill border border-white/30 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.08em] text-on-navy/80">
                    Ejemplo
                </span>
            </div>

            {{-- Tabla real: es informacion tabular, y asi un lector de pantalla
                 anuncia "hora / destino / estado" en cada fila en vez de leer
                 una sucesion de textos sueltos. --}}
            <table class="w-full text-left border-collapse">
                <caption class="sr-only">
                    Muestra ilustrativa de próximas salidas. Consulte el portal de vuelos para información real.
                </caption>
                <thead>
                    <tr class="bg-bg border-b border-border">
                        <th scope="col" class="px-5 py-2.5 font-sans text-[11px] font-bold uppercase tracking-[0.1em] text-muted">Hora</th>
                        <th scope="col" class="px-3 py-2.5 font-sans text-[11px] font-bold uppercase tracking-[0.1em] text-muted">Destino</th>
                        <th scope="col" class="px-5 py-2.5 font-sans text-[11px] font-bold uppercase tracking-[0.1em] text-muted text-right">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salidas as $s)
                        <tr class="border-b border-border last:border-0 transition-colors duration-200 hover:bg-brand-soft/40"
                            data-stagger="flight-row" style="opacity:0;">

                            {{-- La hora es el dato que se busca de un vistazo: va en
                                 Neulis Black y con cifras tabulares para que las
                                 columnas queden alineadas entre filas. --}}
                            <td class="px-5 py-3.5 align-middle">
                                <span class="font-serif text-[19px] leading-none text-brand-navy num-tabular">{{ $s['hora'] }}</span>
                            </td>

                            <td class="px-3 py-3.5 align-middle min-w-0">
                                <div class="flex items-baseline gap-2">
                                    <span class="font-sans text-[15px] font-semibold text-fg truncate">{{ $s['destino'] }}</span>
                                    {{-- El codigo IATA da el aire de tablero de
                                         aeropuerto sin recurrir a adornos. --}}
                                    <span class="shrink-0 font-sans text-[12px] font-bold tracking-[0.08em] text-muted">{{ $s['iata'] }}</span>
                                </div>
                            </td>

                            <td class="px-5 py-3.5 align-middle text-right">
                                <span class="{{ $chips[$s['tono']] ?? 'chip-cerrado' }}">{{ $s['estado'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pie: de donde sale la informacion real --}}
            <div class="bg-bg border-t border-border px-5 py-3">
                <p class="text-[13px] text-muted">
                    Información oficial en
                    <a href="{{ $block->get('cta_url', 'https://tagsa.aero/vuelos') }}" target="_blank" rel="noopener"
                       class="rounded-pill font-semibold text-brand-primary hover:text-brand-navy transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">tagsa.aero</a>
                </p>
            </div>
        </div>
    </div>
</section>
