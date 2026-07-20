@php
    use Illuminate\Support\Facades\Storage;

    $convId         = $block->settings['convocatoria_id'] ?? null;
    $hideWhenClosed = (bool)($block->settings['hide_when_closed'] ?? true);

    $conv = $convId
        ? \App\Models\Convocatoria::find($convId)
        : \App\Models\Convocatoria::featured();

    $tipo            = $conv?->tipo ?? 'proceso';
    $effectiveStatus = $conv ? ($conv->effective_status ?? $conv->status) : null;
    $isOpen          = $effectiveStatus === 'vigente';
    $closes          = $conv?->closes_at;
    $logo            = setting_asset('site_logo');

    // Determinar si hay que ocultar
    $shouldHide = ! $conv
        || $conv->status === 'borrador'
        || ($tipo === 'proceso' && ! $isOpen && $hideWhenClosed);
@endphp

{{-- ════════════════════════════════════════════════════════════════════════════
     ESTADO VACÍO — Sin convocatoria activa
════════════════════════════════════════════════════════════════════════════ --}}
@if($shouldHide)
<section class="bg-bg">
    <div class="section-wrap">
        <div class="grid lg:grid-cols-[1.4fr_1fr] gap-4 items-stretch">
            <div class="card-surface p-6 md:p-8 flex flex-col justify-center" data-aos="fade-up">
                {{-- chip-cerrado en vez del gris suelto: el estado "sin convocatoria"
                     comparte semantica con "cerrado", asi que reusa su color apagado. --}}
                <span class="chip-cerrado w-fit">SIN CONVOCATORIA ACTIVA</span>
                <h2 class="font-serif text-section-title text-brand-navy mt-3">
                    No hay convocatorias abiertas en este momento
                </h2>
                <p class="mt-3 text-[13px] text-muted leading-[1.6] max-w-xl">
                    Cuando se abra un nuevo proceso o se publique un aviso, aparecerá aquí automáticamente.
                </p>
                <div class="mt-5">
                    <a href="/convocatorias" class="btn-ghost">
                        Ver procesos anteriores
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>
            </div>
            {{-- Panel de espera. Se elimino el falso contador "00:00:00:00" de la
                 version anterior: mostrar ceros sugiere un plazo vencido y en B
                 el vacio se comunica con un rotulo, no con un widget inerte. --}}
            <div class="card-surface bg-brand-soft/40 p-6 md:p-8 flex flex-col items-center justify-center gap-3 text-center"
                 data-aos="fade-up" data-aos-delay="150">
                <svg class="w-8 h-8 text-brand-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                <p class="font-sans text-[11px] font-bold uppercase tracking-[0.14em] text-brand-navy">PRÓXIMAMENTE</p>
                <p class="text-[12px] text-muted leading-[1.6] max-w-[24ch]">
                    Los nuevos procesos se publican en esta sección.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════════════════
     AVISO — LAYOUT PÓSTER  (banda navy, centrado, estilo afiche)
════════════════════════════════════════════════════════════════════════════ --}}
@elseif($tipo === 'aviso' && ($block->settings['layout_type'] ?? $conv->layout_type ?? 'poster') === 'poster')
@php
    $imagenUrl = $conv->imagen ? Storage::disk('public')->url($conv->imagen) : null;
    $cronograma = is_array($conv->cronograma) ? $conv->cronograma : [];
@endphp
{{-- Se retiraron los circulos difuminados de fondo: B no tiene formas redondas
     ni degradados decorativos, la banda navy es plana y el ritmo lo dan los
     filetes. El color va por token (bg-brand-navy), no por inline style. --}}
<section class="bg-brand-navy rule-accent">
    <div class="section-wrap">
        <div class="max-w-2xl mx-auto">
            {{-- Header: logo + tipo --}}
            <div class="flex items-center justify-between gap-4 pb-4 border-b border-white/20 mb-8">
                @if($conv->show_logo && $logo)
                    <img src="{{ $logo }}" alt="Autoridad Aeroportuaria de Guayaquil"
                         class="h-9 md:h-10 object-contain brightness-0 invert">
                @endif
                <span class="font-sans text-[11px] tracking-[0.14em] uppercase font-bold text-brand-accent ml-auto">
                    AVISO OFICIAL
                </span>
            </div>

            {{-- Imagen / ícono central --}}
            @if($imagenUrl)
                <div class="flex justify-center mb-7" data-aos="zoom-in" data-aos-duration="600">
                    <div class="w-32 h-32 rounded-card overflow-hidden border border-white/25">
                        <img src="{{ $imagenUrl }}" alt="{{ $conv->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
                    </div>
                </div>
            @else
                <div class="flex justify-center mb-7" data-aos="zoom-in">
                    <div class="w-16 h-16 rounded-card flex items-center justify-center border border-brand-accent/50 bg-brand-accent/10">
                        <svg class="w-8 h-8 text-brand-accent" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>
                        </svg>
                    </div>
                </div>
            @endif

            {{-- Título --}}
            <div class="text-center" data-aos="fade-up" data-aos-duration="700">
                <h2 class="font-serif text-display text-on-navy">
                    {{ $conv->title }}
                </h2>
                @if($conv->short_description)
                    <p class="mt-4 text-on-navy/70 text-sm leading-[1.6] max-w-lg mx-auto">
                        {{ $conv->short_description }}
                    </p>
                @endif
            </div>

            {{-- Video embed --}}
            @if($conv->embed_url)
                <div class="mt-8 aspect-video rounded-card overflow-hidden border border-white/20"
                     data-aos="fade-up">
                    <iframe src="{{ $conv->embed_url }}"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            title="{{ $conv->title }}">
                    </iframe>
                </div>
            @endif

            {{-- Cronograma de fechas --}}
            @if(count($cronograma))
                <div class="mt-8 border-t border-white/20 pt-6" data-aos="fade-up">
                    <p class="font-sans text-[11px] tracking-[0.14em] uppercase text-brand-accent font-bold text-center mb-4">CRONOGRAMA</p>
                    <div>
                        @foreach($cronograma as $item)
                        <div class="flex items-center justify-between gap-4 py-2.5 border-b border-white/10 last:border-0">
                            <div class="flex items-center gap-2.5">
                                {{-- Cuadrado de 6px, no punto: B no usa circulos. --}}
                                <span class="w-1.5 h-1.5 bg-brand-accent flex-shrink-0"></span>
                                <span class="text-on-navy/90 text-[13px] font-semibold">{{ $item['etapa'] ?? '' }}</span>
                            </div>
                            <div class="text-right flex-shrink-0">
                                @if(!empty($item['fecha']))
                                    <span class="text-on-navy/60 text-[12px] num-tabular">
                                        {{ \Carbon\Carbon::parse($item['fecha'])->translatedFormat('d \\d\\e F') }}
                                        @if(!empty($item['hora']))
                                            <span class="ml-1 text-on-navy/40">· {{ $item['hora'] }}</span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Enlace de referencia. Sobre fondo oscuro la accion es AMARILLA (.btn-white). --}}
            @if($conv->enlace_referencia)
                <div class="mt-7 text-center">
                    <a href="{{ $conv->enlace_referencia }}" target="_blank" rel="noopener" class="btn-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                        </svg>
                        Más información
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════════════════
     AVISO — LAYOUT BANNER HORIZONTAL
════════════════════════════════════════════════════════════════════════════ --}}
@elseif($tipo === 'aviso' && ($block->settings['layout_type'] ?? $conv->layout_type ?? 'poster') === 'banner')
@php
    $imagenUrl  = $conv->imagen ? Storage::disk('public')->url($conv->imagen) : null;
    $cronograma = is_array($conv->cronograma) ? $conv->cronograma : [];
@endphp
<section class="bg-bg">
    <div class="section-wrap">
        <div class="grid md:grid-cols-[5fr_7fr] card-surface overflow-hidden"
             data-aos="fade-up" data-aos-duration="700">

            {{-- Panel izquierdo: imagen o banda navy --}}
            <div class="relative min-h-[220px] overflow-hidden flex items-center justify-center bg-brand-navy">
                @if($imagenUrl)
                    <img src="{{ $imagenUrl }}" alt="{{ $conv->title }}" loading="lazy" decoding="async"
                         class="absolute inset-0 w-full h-full object-cover opacity-70 mix-blend-luminosity">
                @endif
                <div class="relative z-10 p-6 flex flex-col items-center justify-center gap-4 text-center h-full">
                    @if($conv->show_logo && $logo)
                        <img src="{{ $logo }}" alt="AAG" class="h-12 object-contain brightness-0 invert">
                    @else
                        <div class="w-14 h-14 rounded-card flex items-center justify-center border border-white/30">
                            <svg class="w-7 h-7 text-white/70" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m-14.456 0c0-1.586.205-3.124.59-4.59"/>
                            </svg>
                        </div>
                    @endif
                    <span class="font-sans text-[11px] tracking-[0.14em] uppercase font-bold text-brand-accent">AVISO OFICIAL</span>
                </div>
            </div>

            {{-- Panel derecho: contenido --}}
            <div class="bg-card p-6 md:p-8 flex flex-col justify-center">
                {{-- El filete de 3px amarillo hace de eyebrow estructural de B. --}}
                <span class="inline-flex items-center gap-2 font-sans text-[11px] tracking-[0.14em] uppercase font-bold text-brand-primary mb-3">
                    <span class="w-5 h-[3px] bg-brand-accent inline-block"></span>
                    Comunicado institucional
                </span>
                <h2 class="font-serif text-section-title text-brand-navy">
                    {{ $conv->title }}
                </h2>
                @if($conv->short_description)
                    <p class="mt-3 text-[13px] text-muted leading-[1.6]">{{ $conv->short_description }}</p>
                @endif

                {{-- Video embed --}}
                @if($conv->embed_url)
                    <div class="mt-5 aspect-video rounded-card overflow-hidden border border-border">
                        <iframe src="{{ $conv->embed_url }}" class="w-full h-full" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen title="{{ $conv->title }}"></iframe>
                    </div>
                @endif

                @if(count($cronograma))
                    <div class="mt-5 border-t border-border">
                        @foreach($cronograma as $item)
                        <div class="flex items-center gap-3 text-[13px] py-2 border-b border-border last:border-0">
                            <span class="w-1.5 h-1.5 bg-brand-accent flex-shrink-0"></span>
                            <span class="font-semibold text-fg flex-1">{{ $item['etapa'] ?? '' }}</span>
                            @if(!empty($item['fecha']))
                                <span class="text-muted text-[12px] num-tabular flex-shrink-0">
                                    {{ \Carbon\Carbon::parse($item['fecha'])->translatedFormat('d M Y') }}
                                    @if(!empty($item['hora'])) · {{ $item['hora'] }} @endif
                                </span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif

                @if($conv->enlace_referencia)
                    <a href="{{ $conv->enlace_referencia }}" target="_blank" rel="noopener"
                       class="mt-5 inline-flex items-center gap-2 self-start font-sans text-[11px] font-bold uppercase tracking-[0.05em] text-brand-primary hover:text-brand-navy transition-colors">
                        Más información
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════════════════
     AVISO — LAYOUT MINIMAL (elegante, texto + logo)
════════════════════════════════════════════════════════════════════════════ --}}
@elseif($tipo === 'aviso' && ($block->settings['layout_type'] ?? $conv->layout_type ?? 'poster') === 'minimal')
@php
    $imagenUrl  = $conv->imagen ? Storage::disk('public')->url($conv->imagen) : null;
    $cronograma = is_array($conv->cronograma) ? $conv->cronograma : [];
@endphp
<section class="bg-bg">
    <div class="section-wrap">
        <div class="max-w-3xl mx-auto" data-aos="fade-up" data-aos-duration="700">
            {{-- Filete vertical de 3px amarillo: es la version en vertical del
                 .rule-accent de B (antes era un degradado redondeado). --}}
            <div class="border-l-[3px] border-brand-accent pl-6 md:pl-8">

                {{-- Logo + institución --}}
                @if($conv->show_logo && $logo)
                    <div class="flex items-center gap-3 mb-5">
                        <img src="{{ $logo }}" alt="AAG" class="h-8 object-contain">
                        <span class="text-[12px] text-muted">
                            Autoridad Aeroportuaria de Guayaquil
                        </span>
                    </div>
                @endif

                {{-- Tipo --}}
                <span class="kicker">AVISO OFICIAL</span>

                {{-- Título --}}
                <h2 class="font-serif text-section-title text-brand-navy mt-2">
                    {{ $conv->title }}
                </h2>

                {{-- Descripción --}}
                @if($conv->short_description)
                    <p class="mt-3 text-[13px] text-muted leading-[1.6]">
                        {{ $conv->short_description }}
                    </p>
                @endif

                {{-- Imagen opcional --}}
                @if($imagenUrl)
                    <div class="mt-5 rounded-card overflow-hidden max-h-64 border border-border">
                        <img src="{{ $imagenUrl }}" alt="{{ $conv->title }}" loading="lazy" decoding="async" class="w-full object-cover">
                    </div>
                @endif

                {{-- Video embed --}}
                @if($conv->embed_url)
                    <div class="mt-5 aspect-video rounded-card overflow-hidden border border-border">
                        <iframe src="{{ $conv->embed_url }}" class="w-full h-full" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen title="{{ $conv->title }}"></iframe>
                    </div>
                @endif

                {{-- Chips de fechas del cronograma --}}
                @if(count($cronograma))
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach($cronograma as $item)
                        <span class="inline-flex items-center gap-2 text-[12px] px-2.5 py-1 rounded-pill border border-border bg-card text-fg">
                            <span class="font-semibold">{{ $item['etapa'] ?? '' }}</span>
                            @if(!empty($item['fecha']))
                                <span class="text-muted num-tabular">
                                    {{ \Carbon\Carbon::parse($item['fecha'])->translatedFormat('d M Y') }}
                                    @if(!empty($item['hora'])) {{ $item['hora'] }} @endif
                                </span>
                            @endif
                        </span>
                        @endforeach
                    </div>
                @endif

                {{-- Enlace --}}
                @if($conv->enlace_referencia)
                    <div class="mt-5">
                        <a href="{{ $conv->enlace_referencia }}" target="_blank" rel="noopener" class="btn-primary">
                            Ver más detalles
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════════════════
     PROCESO — 3 layouts: split | card | minimal
════════════════════════════════════════════════════════════════════════════ --}}
@else
@php
    $cronograma    = is_array($conv->cronograma)   ? $conv->cronograma   : [];
    $documentos    = is_array($conv->documentos)   ? $conv->documentos   : [];
    $requirements  = is_array($conv->requirements) ? $conv->requirements : [];
    $docTotal      = count($documentos) + ($conv->bases_pdf ? 1 : 0);
    // Layout viene de los settings del BLOQUE (editable desde el editor visual)
    // Si no está en settings, cae al modelo, y si tampoco, 'split'
    $procesoLayout = $block->settings['layout_type'] ?? $conv->layout_type ?? 'split';
    $convLink      = route('convocatorias.show', $conv->slug);

    // Chip de estado: se resuelve una sola vez porque los 3 layouts lo pintan.
    // Las clases .chip-* viven en app.css y ya diferencian el color por estado.
    $chipClass = $isOpen ? 'chip-abierto' : 'chip-cerrado';
    $chipLabel = $isOpen ? 'Convocatoria abierta' : 'Convocatoria cerrada';
@endphp
{{-- ── Macro para la lista de documentos (reutilizado en los 3 layouts) ───── --}}
@php
    $renderDocBtn = function() use ($documentos, $docTotal, $conv): string { return ''; };
    // (La lista de documentos se incluye directamente en cada layout)
@endphp

{{-- ════ LAYOUT: SPLIT (recomendado, dos columnas) ════════════════════════ --}}
@if($procesoLayout !== 'card' && $procesoLayout !== 'minimal')
<section class="bg-bg">
    <div class="section-wrap">
        <div class="max-w-5xl mx-auto grid md:grid-cols-[1.3fr_1fr] gap-4 items-start"
             data-aos="fade-up" data-aos-duration="700">

            {{-- ── Columna izquierda: info ──────────────────────────────────── --}}
            <div class="card-surface p-6 md:p-8 flex flex-col gap-5">

                {{-- Logo institucional + chip de estado + título --}}
                <div>
                    @if($logo)
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-border">
                        <img src="{{ $logo }}" alt="{{ settings('site_name','AAG') }}" class="h-8 object-contain">
                        <span class="text-[12px] text-muted leading-snug">
                            {{ settings('site_name','Autoridad Aeroportuaria de Guayaquil') }}
                        </span>
                    </div>
                    @endif
                    <span class="{{ $chipClass }}">
                        {{-- Cuadrado en color heredado del chip: sin circulos ni parpadeo,
                             el color del chip ya distingue abierto de cerrado. --}}
                        <span class="w-1.5 h-1.5 bg-current inline-block"></span>
                        {{ $chipLabel }}
                    </span>
                    <h2 class="font-serif text-section-title text-brand-navy mt-2.5">
                        {{ $conv->title }}
                    </h2>
                    @if($conv->short_description)
                        <p class="mt-2.5 text-[13px] text-muted leading-[1.6]">
                            {{ $conv->short_description }}
                        </p>
                    @endif
                </div>

                {{-- Datos clave: área / modalidad / cierre --}}
                @if($conv->area || $conv->modality || $closes)
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-y border-border py-4">
                    @if($conv->area)
                    <div>
                        <dt class="font-sans text-[10px] tracking-[0.14em] uppercase text-muted font-bold mb-1">ÁREA</dt>
                        <dd class="text-[13px] font-semibold text-fg">{{ $conv->area }}</dd>
                    </div>
                    @endif
                    @if($conv->modality)
                    <div>
                        <dt class="font-sans text-[10px] tracking-[0.14em] uppercase text-muted font-bold mb-1">MODALIDAD</dt>
                        <dd class="text-[13px] font-semibold text-fg">{{ $conv->modality }}</dd>
                    </div>
                    @endif
                    @if($closes)
                    <div class="sm:col-span-2">
                        <dt class="font-sans text-[10px] tracking-[0.14em] uppercase text-muted font-bold mb-1">FECHA DE CIERRE</dt>
                        <dd class="text-[13px] font-semibold text-fg num-tabular">
                            {{ $closes->translatedFormat('d \\d\\e F \\d\\e Y · H:i') }}
                        </dd>
                    </div>
                    @endif
                </dl>
                @endif

                {{-- Cronograma (si existe) --}}
                @if(count($cronograma))
                <div>
                    <p class="font-sans text-[10px] tracking-[0.14em] uppercase text-muted font-bold mb-2.5">CRONOGRAMA</p>
                    <ol>
                        @foreach($cronograma as $idx => $item)
                        <li class="flex items-center gap-3 py-2 border-b border-border last:border-0">
                            {{-- Indice en cuadrado navy, no en circulo celeste. --}}
                            <span class="w-5 h-5 rounded-pill flex items-center justify-center font-sans text-[10px] font-bold flex-shrink-0 bg-brand-soft text-brand-navy">
                                {{ $idx+1 }}
                            </span>
                            <span class="text-[13px] font-semibold text-fg flex-1 min-w-0">{{ $item['etapa'] ?? '' }}</span>
                            @if(!empty($item['fecha']))
                            <span class="text-[12px] text-muted num-tabular flex-shrink-0">
                                {{ \Carbon\Carbon::parse($item['fecha'])->translatedFormat('d M Y') }}
                                @if(!empty($item['hora'])) · {{ $item['hora'] }}@endif
                            </span>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                </div>
                @endif

                {{-- Requisitos mínimos --}}
                @if(count($requirements))
                <div>
                    <p class="font-sans text-[10px] tracking-[0.14em] uppercase text-muted font-bold mb-2.5">REQUISITOS MÍNIMOS</p>
                    <ul class="grid sm:grid-cols-2 gap-x-6 gap-y-1.5">
                        @foreach($requirements as $req)
                        <li class="flex items-start gap-2 text-[13px] text-fg">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-brand-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                            {{ $req }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Botón ver detalles completos (página interna) --}}
                <a href="{{ $convLink }}" class="btn-primary self-start mt-auto">
                    Ver convocatoria completa
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>

            {{-- ── Columna derecha: countdown + documentos ─────────────────────── --}}
            <div class="flex flex-col gap-4 md:sticky md:top-6">

                {{-- Countdown sobre banda navy plana --}}
                <div class="bg-brand-navy rounded-card p-6">
                    @if($isOpen && $closes && $closes->isFuture())
                        <p class="font-sans text-[10px] tracking-[0.14em] uppercase font-bold mb-3 text-brand-accent">TIEMPO RESTANTE</p>
                        {{-- Se retiro el :class de escalado al cambiar de segundo:
                             B es un diseno estatico, sin transformaciones. --}}
                        <div class="grid grid-cols-4 gap-2"
                             x-data="countdown('{{ $closes->toIso8601String() }}')"
                             x-init="start()">
                            @foreach(['days' => 'DÍAS', 'hours' => 'HRS', 'minutes' => 'MIN', 'seconds' => 'SEG'] as $k => $l)
                            <div class="text-center">
                                <div class="rounded-card py-2.5 mb-1.5 border border-white/20 bg-white/[0.06]">
                                    <span class="font-serif text-[26px] leading-none text-on-navy num-tabular block"
                                          x-text="String({{ $k }}).padStart(2,'0')">00</span>
                                </div>
                                <span class="block font-sans text-[10px] tracking-[0.12em] uppercase font-bold text-on-navy/60">{{ $l }}</span>
                            </div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-[11px] text-center text-on-navy/50 num-tabular">
                            Cierre: {{ $closes->translatedFormat('d \\d\\e F \\d\\e Y · H:i') }}
                        </p>
                    @else
                        <div class="text-center py-1">
                            <svg class="w-7 h-7 mx-auto mb-2.5 text-on-navy/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                            </svg>
                            <p class="font-sans text-[11px] font-bold tracking-[0.14em] uppercase text-on-navy/60">Proceso cerrado</p>
                            @if($closes)
                            <p class="font-serif text-[18px] text-on-navy/70 mt-1 num-tabular">
                                {{ $closes->translatedFormat('d \\d\\e F · H:i') }}
                            </p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Documentos --}}
                @if($docTotal > 0)
                <div class="card-surface overflow-hidden" x-data="{ open: false }">
                    <button type="button" @click="open = !open" :aria-expanded="open"
                            class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left transition-colors hover:bg-brand-soft/40"
                            :class="open ? 'bg-brand-soft/40' : ''">
                        <div>
                            <p class="text-[13px] font-bold text-brand-navy">Documentos del proceso</p>
                            <p class="text-[11px] text-muted">{{ $docTotal }} archivo{{ $docTotal !== 1 ? 's' : '' }}</p>
                        </div>
                        {{-- aria-hidden: el estado ya lo comunica aria-expanded. --}}
                        <span class="shrink-0 font-sans text-[15px] font-bold leading-none w-4 text-center transition-colors"
                              :class="open ? 'text-brand-primary' : 'text-muted'"
                              x-text="open ? '−' : '+'" aria-hidden="true">+</span>
                    </button>
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         style="display:none;">
                        <div class="border-t border-border">
                            @if($conv->bases_pdf)
                            @php $info = \App\Models\Convocatoria::fileTypeInfo($conv->bases_pdf); @endphp
                            <div class="flex items-center gap-3 px-4 py-2.5 border-b border-border last:border-0">
                                <span class="w-8 h-8 rounded-pill flex items-center justify-center text-[10px] font-bold border border-border flex-shrink-0 bg-brand-soft text-brand-navy">{{ $info['label'] }}</span>
                                <span class="flex-1 text-[13px] font-semibold text-fg truncate">Bases del proceso</span>
                                <a href="{{ Storage::disk('public')->url($conv->bases_pdf) }}" target="_blank" download
                                   class="flex-shrink-0 font-sans text-[11px] font-bold uppercase tracking-[0.05em] text-brand-primary hover:text-brand-navy transition-colors">Descargar</a>
                            </div>
                            @endif
                            @foreach($documentos as $doc)
                            @php
                                $archivo = $doc['archivo'] ?? $doc['path'] ?? '';
                                $nombre  = $doc['nombre'] ?? basename($archivo);
                                $info    = \App\Models\Convocatoria::fileTypeInfo($archivo);
                                $url     = $archivo ? Storage::disk('public')->url($archivo) : '#';
                            @endphp
                            @if($archivo)
                            <div class="flex items-center gap-3 px-4 py-2.5 border-b border-border last:border-0">
                                <span class="w-8 h-8 rounded-pill flex items-center justify-center text-[10px] font-bold border border-border flex-shrink-0 bg-brand-soft text-brand-navy">{{ $info['label'] }}</span>
                                <span class="flex-1 text-[13px] font-semibold text-fg truncate" title="{{ $nombre }}">{{ $nombre }}</span>
                                <a href="{{ $url }}" target="_blank" download
                                   class="flex-shrink-0 font-sans text-[11px] font-bold uppercase tracking-[0.05em] text-brand-primary hover:text-brand-navy transition-colors">Descargar</a>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ════ LAYOUT: CARD (tarjeta única, header navy) ════════════════════════ --}}
@elseif($procesoLayout === 'card')
<section class="bg-bg">
    <div class="section-wrap">
        <div class="max-w-4xl mx-auto card-surface overflow-hidden"
             data-aos="fade-up" data-aos-duration="700">

            {{-- Header navy plano, cerrado por el filete amarillo de 3px --}}
            <div class="bg-brand-navy rule-accent px-6 md:px-8 py-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-5">
                    <div class="flex-1 min-w-0">
                        {{-- Sobre navy los .chip-* apagados siguen legibles (fondo claro,
                             texto oscuro), asi que se usa la misma clase que en split. --}}
                        <span class="{{ $chipClass }} mb-3">
                            <span class="w-1.5 h-1.5 bg-current inline-block"></span>
                            {{ $chipLabel }}
                        </span>
                        <h2 class="font-serif text-section-title text-on-navy">{{ $conv->title }}</h2>
                        @if($conv->short_description)
                        <p class="mt-2.5 text-[13px] leading-[1.6] text-on-navy/70">{{ $conv->short_description }}</p>
                        @endif
                    </div>
                    @if($isOpen && $closes && $closes->isFuture())
                    <div class="flex-shrink-0" x-data="countdown('{{ $closes->toIso8601String() }}')" x-init="start()">
                        <p class="font-sans text-[10px] tracking-[0.12em] uppercase font-bold mb-2 text-brand-accent">TIEMPO RESTANTE</p>
                        <div class="flex items-start gap-1.5">
                            @foreach(['days' => 'DÍAS', 'hours' => 'HRS', 'minutes' => 'MIN', 'seconds' => 'SEG'] as $k => $l)
                            <div class="text-center">
                                <div class="w-11 h-11 rounded-card flex items-center justify-center mb-1 border border-white/20 bg-white/[0.06]">
                                    <span class="font-serif text-[19px] text-on-navy num-tabular"
                                          x-text="String({{ $k }}).padStart(2,'0')">00</span>
                                </div>
                                <span class="block font-sans text-[10px] tracking-[0.1em] uppercase font-bold text-on-navy/60">{{ $l }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Cuerpo blanco --}}
            <div class="bg-card divide-y divide-border">
                {{-- Meta info (área/modalidad/cierre) --}}
                @if($conv->area || $conv->modality || $closes)
                <dl class="grid sm:grid-cols-3 gap-0 divide-x divide-border">
                    @if($conv->area)
                    <div class="px-6 py-4">
                        <dt class="font-sans text-[10px] tracking-[0.14em] uppercase text-muted font-bold mb-1">ÁREA</dt>
                        <dd class="text-[13px] font-semibold text-fg">{{ $conv->area }}</dd>
                    </div>
                    @endif
                    @if($conv->modality)
                    <div class="px-6 py-4">
                        <dt class="font-sans text-[10px] tracking-[0.14em] uppercase text-muted font-bold mb-1">MODALIDAD</dt>
                        <dd class="text-[13px] font-semibold text-fg">{{ $conv->modality }}</dd>
                    </div>
                    @endif
                    @if($closes)
                    <div class="px-6 py-4">
                        <dt class="font-sans text-[10px] tracking-[0.14em] uppercase text-muted font-bold mb-1">FECHA DE CIERRE</dt>
                        <dd class="text-[12px] font-semibold text-fg num-tabular leading-snug">{{ $closes->translatedFormat('d \\d\\e F \\d\\e Y · H:i') }}</dd>
                    </div>
                    @endif
                </dl>
                @endif
                {{-- Cronograma --}}
                @if(count($cronograma))
                <div class="px-6 md:px-8 py-5">
                    <p class="font-sans text-[10px] tracking-[0.14em] uppercase text-muted font-bold mb-3">CRONOGRAMA</p>
                    <ol>
                        @foreach($cronograma as $idx => $item)
                        <li class="flex items-center justify-between gap-3 py-2 border-b border-border last:border-0">
                            <span class="flex items-center gap-2.5 min-w-0">
                                <span class="w-5 h-5 rounded-pill flex items-center justify-center font-sans text-[10px] font-bold flex-shrink-0 bg-brand-soft text-brand-navy">{{ $idx+1 }}</span>
                                <span class="text-[13px] font-semibold text-fg truncate">{{ $item['etapa'] ?? '' }}</span>
                            </span>
                            @if(!empty($item['fecha']))
                            <span class="text-[12px] text-muted num-tabular flex-shrink-0">{{ \Carbon\Carbon::parse($item['fecha'])->translatedFormat('d M Y') }}@if(!empty($item['hora'])) · {{ $item['hora'] }}@endif</span>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                </div>
                @endif
                {{-- Requisitos --}}
                @if(count($requirements))
                <div class="px-6 md:px-8 py-5">
                    <p class="font-sans text-[10px] tracking-[0.14em] uppercase text-muted font-bold mb-2.5">REQUISITOS MÍNIMOS</p>
                    <ul class="grid sm:grid-cols-2 gap-x-6 gap-y-1.5">
                        @foreach($requirements as $req)
                        <li class="flex items-start gap-2 text-[13px] text-fg">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-brand-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            {{ $req }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                {{-- Documentos --}}
                @if($docTotal > 0)
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" :aria-expanded="open"
                            class="w-full flex items-center justify-between gap-3 px-6 md:px-8 py-4 text-left transition-colors hover:bg-brand-soft/40"
                            :class="open ? 'bg-brand-soft/40' : ''">
                        <span>
                            <span class="block text-[13px] font-bold text-brand-navy">Documentos del proceso</span>
                            <span class="block text-[11px] text-muted">{{ $docTotal }} archivo{{ $docTotal !== 1 ? 's' : '' }}</span>
                        </span>
                        <span class="shrink-0 font-sans text-[15px] font-bold leading-none w-4 text-center transition-colors"
                              :class="open ? 'text-brand-primary' : 'text-muted'"
                              x-text="open ? '−' : '+'" aria-hidden="true">+</span>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150" style="display:none;">
                        <div class="border-t border-border">
                            @if($conv->bases_pdf)
                            @php $info = \App\Models\Convocatoria::fileTypeInfo($conv->bases_pdf); @endphp
                            <div class="flex items-center gap-3 px-6 md:px-8 py-2.5 border-b border-border last:border-0">
                                <span class="w-8 h-8 rounded-pill flex items-center justify-center text-[10px] font-bold border border-border flex-shrink-0 bg-brand-soft text-brand-navy">{{ $info['label'] }}</span>
                                <span class="flex-1 text-[13px] font-semibold text-fg truncate">Bases del proceso</span>
                                <a href="{{ Storage::disk('public')->url($conv->bases_pdf) }}" target="_blank" download
                                   class="flex-shrink-0 font-sans text-[11px] font-bold uppercase tracking-[0.05em] text-brand-primary hover:text-brand-navy transition-colors">Descargar</a>
                            </div>
                            @endif
                            @foreach($documentos as $doc)
                            @php $archivo=$doc['archivo']??$doc['path']??''; $nombre=$doc['nombre']??basename($archivo); $info=\App\Models\Convocatoria::fileTypeInfo($archivo); $url=$archivo?Storage::disk('public')->url($archivo):'#'; @endphp
                            @if($archivo)
                            <div class="flex items-center gap-3 px-6 md:px-8 py-2.5 border-b border-border last:border-0">
                                <span class="w-8 h-8 rounded-pill flex items-center justify-center text-[10px] font-bold border border-border flex-shrink-0 bg-brand-soft text-brand-navy">{{ $info['label'] }}</span>
                                <span class="flex-1 text-[13px] font-semibold text-fg truncate" title="{{ $nombre }}">{{ $nombre }}</span>
                                <a href="{{ $url }}" target="_blank" download
                                   class="flex-shrink-0 font-sans text-[11px] font-bold uppercase tracking-[0.05em] text-brand-primary hover:text-brand-navy transition-colors">Descargar</a>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                {{-- Footer: enlace interno --}}
                <div class="px-6 md:px-8 py-4 bg-bg flex items-center justify-between gap-4">
                    @if($logo)
                    <img src="{{ $logo }}" alt="{{ settings('site_name','AAG') }}" class="h-6 object-contain">
                    @endif
                    <a href="{{ $convLink }}" class="btn-primary ml-auto">
                        Ver convocatoria completa
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════ LAYOUT: MINIMAL (filete izquierdo, limpio) ═══════════════════════ --}}
@else
<section class="bg-bg">
    <div class="section-wrap">
        <div class="max-w-3xl" data-aos="fade-up" data-aos-duration="700">
            <div class="border-l-[3px] border-brand-accent pl-6">

                <span class="kicker">PROCESO DE CONTRATACIÓN</span>

                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mt-2">
                    <div>
                        <span class="{{ $chipClass }} mb-2">
                            <span class="w-1.5 h-1.5 bg-current inline-block"></span>
                            {{ $chipLabel }}
                        </span>
                        <h2 class="font-serif text-section-title text-brand-navy">{{ $conv->title }}</h2>
                        @if($conv->short_description)
                        <p class="mt-2 text-[13px] text-muted leading-[1.6]">{{ $conv->short_description }}</p>
                        @endif
                    </div>
                    @if($isOpen && $closes && $closes->isFuture())
                    <div class="flex-shrink-0 rounded-card px-4 py-3 text-center card-surface"
                         x-data="countdown('{{ $closes->toIso8601String() }}')" x-init="start()">
                        <p class="font-sans text-[10px] tracking-[0.12em] uppercase text-muted font-bold mb-1.5">TIEMPO RESTANTE</p>
                        <div class="flex items-start gap-2 justify-center">
                            @foreach(['days' => 'D', 'hours' => 'H', 'minutes' => 'M', 'seconds' => 'S'] as $k => $l)
                            <div class="text-center">
                                <span class="font-serif text-[19px] text-brand-navy num-tabular block leading-none"
                                      x-text="String({{ $k }}).padStart(2,'0')">00</span>
                                <span class="font-sans text-[10px] font-bold text-muted">{{ $l }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Meta chips --}}
                @if($conv->area || $conv->modality || $closes)
                <div class="flex flex-wrap gap-2 mt-4">
                    @if($conv->area)
                    <span class="inline-flex items-center gap-1.5 text-[12px] px-2.5 py-1 rounded-pill border border-border bg-card text-fg">
                        {{ $conv->area }}
                    </span>
                    @endif
                    @if($conv->modality)
                    <span class="inline-flex items-center gap-1.5 text-[12px] px-2.5 py-1 rounded-pill border border-border bg-card text-fg">
                        {{ $conv->modality }}
                    </span>
                    @endif
                    @if($closes)
                    <span class="inline-flex items-center gap-1.5 text-[12px] px-2.5 py-1 rounded-pill border border-border bg-card text-fg num-tabular">
                        Cierre: {{ $closes->translatedFormat('d M Y · H:i') }}
                    </span>
                    @endif
                </div>
                @endif

                {{-- Documentos accordion mínimal --}}
                @if($docTotal > 0)
                <div class="mt-4 card-surface overflow-hidden" x-data="{ open: false }">
                    <button type="button" @click="open = !open" :aria-expanded="open"
                            class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left text-[13px] font-bold text-brand-navy hover:bg-brand-soft/40 transition-colors">
                        <span>Documentos ({{ $docTotal }})</span>
                        <span class="shrink-0 font-sans text-[15px] font-bold leading-none w-4 text-center transition-colors"
                              :class="open ? 'text-brand-primary' : 'text-muted'"
                              x-text="open ? '−' : '+'" aria-hidden="true">+</span>
                    </button>
                    <div x-show="open" x-transition style="display:none;">
                        <div class="border-t border-border">
                            @if($conv->bases_pdf)
                            @php $info = \App\Models\Convocatoria::fileTypeInfo($conv->bases_pdf); @endphp
                            <div class="flex items-center gap-3 px-4 py-2.5 border-b border-border last:border-0">
                                <span class="w-8 h-8 rounded-pill flex items-center justify-center text-[10px] font-bold border border-border flex-shrink-0 bg-brand-soft text-brand-navy">{{ $info['label'] }}</span>
                                <span class="flex-1 text-[13px] text-fg truncate">Bases del proceso</span>
                                <a href="{{ Storage::disk('public')->url($conv->bases_pdf) }}" target="_blank" download
                                   class="flex-shrink-0 font-sans text-[11px] font-bold uppercase tracking-[0.05em] text-brand-primary hover:text-brand-navy transition-colors">Descargar</a>
                            </div>
                            @endif
                            @foreach($documentos as $doc)
                            @php $archivo=$doc['archivo']??$doc['path']??''; $nombre=$doc['nombre']??basename($archivo); $info=\App\Models\Convocatoria::fileTypeInfo($archivo); $url=$archivo?Storage::disk('public')->url($archivo):'#'; @endphp
                            @if($archivo)
                            <div class="flex items-center gap-3 px-4 py-2.5 border-b border-border last:border-0">
                                <span class="w-8 h-8 rounded-pill flex items-center justify-center text-[10px] font-bold border border-border flex-shrink-0 bg-brand-soft text-brand-navy">{{ $info['label'] }}</span>
                                <span class="flex-1 text-[13px] text-fg truncate" title="{{ $nombre }}">{{ $nombre }}</span>
                                <a href="{{ $url }}" target="_blank" download
                                   class="flex-shrink-0 font-sans text-[11px] font-bold uppercase tracking-[0.05em] text-brand-primary hover:text-brand-navy transition-colors">Descargar</a>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-4 flex items-center gap-3">
                    @if($logo)
                    <img src="{{ $logo }}" alt="{{ settings('site_name','AAG') }}" class="h-6 object-contain">
                    @endif
                    <a href="{{ $convLink }}"
                       class="inline-flex items-center gap-2 font-sans text-[11px] font-bold uppercase tracking-[0.05em] text-brand-primary hover:text-brand-navy transition-colors">
                        Ver convocatoria completa y documentos
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif{{-- /procesoLayout --}}
@endif{{-- /tipo --}}
