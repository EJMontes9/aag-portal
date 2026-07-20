@props(['block'])

{{-- Estado de vuelos — Propuesta B.

     DECISION DE DISENO IMPORTANTE:
     Este panel NO muestra horarios ni destinos legibles, a proposito.

     La AAG no expone un servicio propio de vuelos: la informacion real vive
     en el portal de TAGSA. Cualquier hora concreta que se pinte aqui —aunque
     sea de ejemplo— puede llevar a un pasajero a creer que su vuelo sale a
     esa hora. En un portal institucional eso no es un detalle estetico, es
     informacion que la gente usa para tomar decisiones.

     Por eso el tablero se representa como una silueta difuminada: se reconoce
     al instante que ahi hay un panel de salidas, pero no hay ningun dato que
     leer ni con el que equivocarse. Encima va la llamada a la accion hacia la
     fuente oficial, que es el objetivo real del bloque.

     Si algun dia se conecta una fuente de datos en vivo, se sustituye la
     silueta por las filas reales y se retira el velo. --}}
@php
    // Anchos de las barras de la silueta. Fijos y no aleatorios: el panel debe
    // verse igual en cada visita, si no parece que algo esta cargando.
    $filas = [
        ['hora' => 'w-12', 'destino' => 'w-28', 'estado' => 'w-20'],
        ['hora' => 'w-12', 'destino' => 'w-36', 'estado' => 'w-24'],
        ['hora' => 'w-12', 'destino' => 'w-24', 'estado' => 'w-20'],
        ['hora' => 'w-12', 'destino' => 'w-32', 'estado' => 'w-16'],
    ];
    $urlVuelos = $block->get('cta_url', 'https://tagsa.aero/vuelos');
@endphp

<section id="vuelos" class="bg-brand-navy text-on-navy">
    <div class="section-wrap grid lg:grid-cols-[1fr_1.1fr] gap-8 lg:gap-14 items-center">

        {{-- ── Columna de texto ──────────────────────────────────────────── --}}
        <div data-aos="fade-right" data-aos-duration="700">
            @if($block->get('kicker'))
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
                    <a href="{{ $urlVuelos }}" target="_blank" rel="noopener" class="btn-white">
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

        {{-- ── Panel: silueta de tablero + acceso a la fuente oficial ────── --}}
        <div class="card-surface overflow-hidden" data-aos="fade-left" data-aos-duration="700" data-aos-delay="150">

            {{-- Cabecera navy con el filete amarillo --}}
            <div class="bg-brand-navy rule-accent px-5 py-3.5 flex items-center gap-2.5">
                <svg class="w-4 h-4 text-brand-accent shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                </svg>
                <span class="font-sans text-[13px] font-bold uppercase tracking-[0.12em] text-on-navy">
                    Salidas y llegadas
                </span>
            </div>

            <div class="relative">

                {{-- Silueta difuminada del tablero.
                     aria-hidden porque no comunica nada: es una figura, no
                     informacion. El contenido util para un lector de pantalla
                     es el texto del velo que va debajo. --}}
                <div class="px-5 py-4 select-none" aria-hidden="true"
                     style="filter: blur(4px); opacity: 0.5;">
                    {{-- Cabecera de columnas simulada --}}
                    <div class="flex items-center gap-4 pb-2.5 border-b border-border">
                        <span class="h-2 w-10 rounded-pill bg-border"></span>
                        <span class="h-2 w-16 rounded-pill bg-border"></span>
                        <span class="h-2 w-14 rounded-pill bg-border ml-auto"></span>
                    </div>

                    @foreach($filas as $f)
                        <div class="flex items-center gap-4 py-3.5 border-b border-border last:border-0">
                            {{-- La "hora": una barra navy mas marcada, que es lo
                                 que da la lectura de tablero --}}
                            <span class="h-3.5 {{ $f['hora'] }} rounded-pill bg-brand-navy/35"></span>
                            <span class="h-3 {{ $f['destino'] }} rounded-pill bg-border"></span>
                            <span class="h-4 {{ $f['estado'] }} rounded-pill bg-brand-soft ml-auto"></span>
                        </div>
                    @endforeach
                </div>

                {{-- Velo con la llamada a la accion.
                     Es el contenido real del panel: explica que el dato esta
                     fuera y lleva alli. --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 px-6 text-center"
                     style="background: rgb(255 255 255 / 0.72);">
                    <span class="flex items-center justify-center w-11 h-11 rounded-card bg-brand-soft">
                        <svg class="w-6 h-6 text-brand-primary" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                    </span>

                    <p class="font-sans text-[15px] font-bold text-brand-navy leading-snug max-w-[30ch]">
                        El estado de los vuelos se consulta en el portal de TAGSA
                    </p>
                    <p class="text-[13px] text-muted leading-relaxed max-w-[34ch]">
                        Allí encontrarás los horarios de salidas y llegadas actualizados al momento.
                    </p>

                    <a href="{{ $urlVuelos }}" target="_blank" rel="noopener" class="btn-primary mt-1">
                        Ver vuelos
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Pie: deja explicito que aqui no hay datos, por si alguien se
                 queda mirando la silueta --}}
            <div class="bg-bg border-t border-border px-5 py-3">
                <p class="text-[13px] text-muted text-center">
                    Esta vista es ilustrativa: no muestra horarios reales.
                </p>
            </div>
        </div>
    </div>
</section>
