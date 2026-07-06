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
<section class="bg-brand-soft/20">
    <div class="section-wrap">
        <div class="grid lg:grid-cols-[1.1fr_1fr] gap-10 items-stretch">
            <div class="bg-card border border-border rounded-hero p-8 md:p-10 flex flex-col justify-center" data-aos="fade-up">
                <span class="pill bg-gray-100 text-gray-500 w-fit text-xs">SIN CONVOCATORIA ACTIVA</span>
                <h2 class="font-serif text-3xl md:text-4xl mt-4 text-fg/60" style="font-weight:400;">
                    No hay convocatorias abiertas en este momento
                </h2>
                <p class="mt-4 text-muted text-sm leading-relaxed">
                    Cuando se abra un nuevo proceso o se publique un aviso, aparecerá aquí automáticamente.
                </p>
                <div class="mt-7">
                    <a href="/convocatorias" class="btn-ghost text-sm">
                        Ver procesos anteriores
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="bg-brand-soft/30 border border-dashed border-border rounded-hero p-8 md:p-10 flex flex-col items-center justify-center gap-5" data-aos="fade-up" data-aos-delay="150">
                <div class="w-14 h-14 rounded-full bg-white/60 border border-border flex items-center justify-center">
                    <svg class="w-7 h-7 text-muted/50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <p class="text-[11px] tracking-[0.16em] uppercase text-muted font-semibold">PRÓXIMAMENTE</p>
                <div class="grid grid-cols-4 gap-2 w-full max-w-xs">
                    @foreach(['DÍAS', 'HORAS', 'MIN', 'SEG'] as $lbl)
                    <div class="bg-white/60 rounded-lg p-2.5 text-center border border-border/50">
                        <div class="font-serif text-2xl text-fg/30" style="font-weight:400;">00</div>
                        <div class="text-[9px] tracking-widest uppercase text-muted/60 mt-0.5">{{ $lbl }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════════════════
     AVISO — LAYOUT PÓSTER  (fondo navy, centrado, estilo afiche)
════════════════════════════════════════════════════════════════════════════ --}}
@elseif($tipo === 'aviso' && ($block->settings['layout_type'] ?? $conv->layout_type ?? 'poster') === 'poster')
@php
    $imagenUrl = $conv->imagen ? Storage::disk('public')->url($conv->imagen) : null;
    $cronograma = is_array($conv->cronograma) ? $conv->cronograma : [];
@endphp
<section class="relative overflow-hidden py-16 md:py-24"
         style="background: rgb(var(--color-navy));">
    {{-- Decoración geométrica de fondo --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full opacity-[0.07]"
             style="background: rgb(var(--color-accent));"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full opacity-[0.06]"
             style="background: rgb(var(--color-soft));"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rotate-45 opacity-[0.03] border-2"
             style="border-color: rgb(var(--color-soft));"></div>
    </div>

    <div class="section-wrap relative z-10">
        <div class="max-w-2xl mx-auto">
            {{-- Header: logo + tipo --}}
            <div class="flex items-center justify-between mb-8">
                @if($conv->show_logo && $logo)
                    <img src="{{ $logo }}" alt="Autoridad Aeroportuaria de Guayaquil"
                         class="h-10 md:h-12 object-contain brightness-0 invert opacity-90">
                @endif
                <span class="text-[10px] tracking-[0.22em] uppercase font-bold text-white/50">
                    AVISO OFICIAL
                </span>
            </div>

            {{-- Divider --}}
            <div class="border-t border-white/20 mb-10"></div>

            {{-- Imagen / ícono central --}}
            @if($imagenUrl)
                <div class="flex justify-center mb-8" data-aos="zoom-in" data-aos-duration="600">
                    <div class="w-32 h-32 rounded-2xl overflow-hidden shadow-2xl ring-4 ring-white/20">
                        <img src="{{ $imagenUrl }}" alt="{{ $conv->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
                    </div>
                </div>
            @else
                <div class="flex justify-center mb-8" data-aos="zoom-in">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center"
                         style="background: rgb(var(--color-accent) / 0.15); border: 2px solid rgb(var(--color-accent) / 0.4);">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                             style="color: rgb(var(--color-accent));">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>
                        </svg>
                    </div>
                </div>
            @endif

            {{-- Título --}}
            <div class="text-center" data-aos="fade-up" data-aos-duration="700">
                <h2 class="font-serif text-3xl md:text-5xl text-white leading-tight" style="font-weight:500;">
                    {{ $conv->title }}
                </h2>
                @if($conv->short_description)
                    <p class="mt-5 text-white/70 text-lg leading-relaxed max-w-lg mx-auto">
                        {{ $conv->short_description }}
                    </p>
                @endif
            </div>

            {{-- Video embed --}}
            @if($conv->embed_url)
                <div class="mt-10 aspect-video rounded-2xl overflow-hidden shadow-2xl ring-4 ring-white/10"
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
                <div class="mt-10 border-t border-white/20 pt-8" data-aos="fade-up">
                    <p class="text-[10px] tracking-[0.2em] uppercase text-white/40 font-semibold text-center mb-5">CRONOGRAMA</p>
                    <div class="space-y-3">
                        @foreach($cronograma as $item)
                        <div class="flex items-center justify-between gap-4 py-3 border-b border-white/10 last:border-0">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                     style="background: rgb(var(--color-accent));"></div>
                                <span class="text-white/85 text-sm font-medium">{{ $item['etapa'] ?? '' }}</span>
                            </div>
                            <div class="text-right text-sm flex-shrink-0">
                                @if(!empty($item['fecha']))
                                    <span class="text-white/60 font-mono">
                                        {{ \Carbon\Carbon::parse($item['fecha'])->translatedFormat('d \\d\\e F') }}
                                        @if(!empty($item['hora']))
                                            <span class="ml-1 text-white/40">· {{ $item['hora'] }}</span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Enlace de referencia --}}
            @if($conv->enlace_referencia)
                <div class="mt-8 text-center">
                    <a href="{{ $conv->enlace_referencia }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 text-sm font-semibold px-6 py-3 rounded-full transition-all duration-200"
                       style="background: rgb(var(--color-accent)); color: rgb(var(--color-navy));">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
        <div class="grid md:grid-cols-[5fr_7fr] rounded-2xl overflow-hidden shadow-xl border border-border"
             data-aos="fade-up" data-aos-duration="700">

            {{-- Panel izquierdo: imagen o panel de color --}}
            <div class="relative min-h-[250px] md:min-h-[auto] overflow-hidden flex items-center justify-center"
                 style="background: rgb(var(--color-navy));">
                @if($imagenUrl)
                    <img src="{{ $imagenUrl }}" alt="{{ $conv->title }}" loading="lazy" decoding="async"
                         class="absolute inset-0 w-full h-full object-cover opacity-70 mix-blend-luminosity">
                @endif
                {{-- Overlay + logo --}}
                <div class="relative z-10 p-8 flex flex-col items-center justify-center gap-6 text-center h-full">
                    @if($conv->show_logo && $logo)
                        <img src="{{ $logo }}" alt="AAG" class="h-14 object-contain brightness-0 invert">
                    @else
                        <div class="w-16 h-16 rounded-full flex items-center justify-center border-2 border-white/30">
                            <svg class="w-8 h-8 text-white/70" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m-14.456 0c0-1.586.205-3.124.59-4.59"/>
                            </svg>
                        </div>
                    @endif
                    <span class="text-[10px] tracking-[0.22em] uppercase font-bold text-white/50">AVISO OFICIAL</span>
                </div>
            </div>

            {{-- Panel derecho: contenido --}}
            <div class="bg-card p-8 md:p-10 flex flex-col justify-center">
                <span class="inline-flex items-center gap-1.5 text-[10px] tracking-[0.18em] uppercase font-semibold mb-4"
                      style="color: rgb(var(--color-accent));">
                    <span class="w-3 h-0.5 rounded-full inline-block"
                          style="background: rgb(var(--color-accent));"></span>
                    Comunicado institucional
                </span>
                <h2 class="font-serif text-2xl md:text-3xl text-fg" style="font-weight:500;">
                    {{ $conv->title }}
                </h2>
                @if($conv->short_description)
                    <p class="mt-4 text-muted leading-relaxed">{{ $conv->short_description }}</p>
                @endif

                {{-- Video embed --}}
                @if($conv->embed_url)
                    <div class="mt-6 aspect-video rounded-xl overflow-hidden shadow-md">
                        <iframe src="{{ $conv->embed_url }}" class="w-full h-full" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen title="{{ $conv->title }}"></iframe>
                    </div>
                @endif

                @if(count($cronograma))
                    <div class="mt-6 space-y-2">
                        @foreach($cronograma as $item)
                        <div class="flex items-center gap-3 text-sm py-2 border-b border-border last:border-0">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                                 style="background: rgb(var(--color-soft) / 0.5);">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                     style="color: rgb(var(--color-primary));">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                                </svg>
                            </div>
                            <span class="font-medium text-fg flex-1">{{ $item['etapa'] ?? '' }}</span>
                            @if(!empty($item['fecha']))
                                <span class="text-muted font-mono text-xs">
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
                       class="mt-6 inline-flex items-center gap-2 text-sm font-semibold self-start"
                       style="color: rgb(var(--color-primary));">
                        Más información
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
            <div class="relative pl-8 md:pl-10">
                {{-- Borde izquierdo de acento --}}
                <div class="absolute left-0 top-0 bottom-0 w-1 rounded-full"
                     style="background: linear-gradient(to bottom, rgb(var(--color-navy)), rgb(var(--color-accent)));"></div>

                {{-- Logo + institución --}}
                @if($conv->show_logo && $logo)
                    <div class="flex items-center gap-3 mb-6">
                        <img src="{{ $logo }}" alt="AAG" class="h-8 object-contain">
                        <span class="text-xs text-muted font-medium tracking-wide">
                            Autoridad Aeroportuaria de Guayaquil
                        </span>
                    </div>
                @endif

                {{-- Tipo --}}
                <span class="text-[10px] tracking-[0.22em] uppercase font-bold"
                      style="color: rgb(var(--color-accent));">
                    AVISO OFICIAL
                </span>

                {{-- Título --}}
                <h2 class="font-serif text-3xl md:text-4xl text-fg mt-3 leading-snug" style="font-weight:500;">
                    {{ $conv->title }}
                </h2>

                {{-- Descripción --}}
                @if($conv->short_description)
                    <p class="mt-4 text-muted text-lg leading-relaxed">
                        {{ $conv->short_description }}
                    </p>
                @endif

                {{-- Imagen opcional --}}
                @if($imagenUrl)
                    <div class="mt-6 rounded-xl overflow-hidden max-h-64 shadow-sm">
                        <img src="{{ $imagenUrl }}" alt="{{ $conv->title }}" loading="lazy" decoding="async" class="w-full object-cover">
                    </div>
                @endif

                {{-- Video embed --}}
                @if($conv->embed_url)
                    <div class="mt-6 aspect-video rounded-xl overflow-hidden shadow-md">
                        <iframe src="{{ $conv->embed_url }}" class="w-full h-full" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen title="{{ $conv->title }}"></iframe>
                    </div>
                @endif

                {{-- Chips de fechas del cronograma --}}
                @if(count($cronograma))
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach($cronograma as $item)
                        <span class="inline-flex items-center gap-2 text-xs font-medium px-3 py-1.5 rounded-full border border-border bg-card text-fg">
                            <svg class="w-3.5 h-3.5 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5"/>
                            </svg>
                            {{ $item['etapa'] ?? '' }}
                            @if(!empty($item['fecha']))
                                <span class="text-muted">
                                    — {{ \Carbon\Carbon::parse($item['fecha'])->translatedFormat('d M Y') }}
                                    @if(!empty($item['hora'])) {{ $item['hora'] }} @endif
                                </span>
                            @endif
                        </span>
                        @endforeach
                    </div>
                @endif

                {{-- Enlace --}}
                @if($conv->enlace_referencia)
                    <div class="mt-6">
                        <a href="{{ $conv->enlace_referencia }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 text-sm font-semibold border-b-2 pb-0.5 transition-colors"
                           style="color: rgb(var(--color-primary)); border-color: rgb(var(--color-accent));">
                            Ver más detalles
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
@endphp
{{-- ── Macro para la lista de documentos (reutilizado en los 3 layouts) ───── --}}
@php
    $renderDocBtn = function() use ($documentos, $docTotal, $conv): string { return ''; };
    // (La lista de documentos se incluye directamente en cada layout)
@endphp

{{-- ════ LAYOUT: SPLIT (recomendado, dos columnas) ════════════════════════ --}}
@if($procesoLayout !== 'card' && $procesoLayout !== 'minimal')
<section class="bg-brand-soft/20">
    <div class="section-wrap">
        <div class="max-w-5xl mx-auto grid md:grid-cols-[1.3fr_1fr] gap-5 md:gap-7 items-start"
             data-aos="fade-up" data-aos-duration="700">

            {{-- ── Columna izquierda: info ──────────────────────────────────── --}}
            <div class="bg-card border border-border rounded-2xl px-8 py-7 md:px-10 md:py-9 flex flex-col gap-5">

                {{-- Logo institucional + badge + título --}}
                <div>
                    @if($logo)
                    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-border">
                        <img src="{{ $logo }}" alt="{{ settings('site_name','AAG') }}" class="h-9 object-contain">
                        <span class="text-xs text-muted font-medium leading-snug">
                            {{ settings('site_name','Autoridad Aeroportuaria de Guayaquil') }}
                        </span>
                    </div>
                    @endif
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border
                          {{ $isOpen ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $isOpen ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                        {{ $isOpen ? 'Convocatoria abierta' : 'Convocatoria cerrada' }}
                    </span>
                    <h2 class="font-serif text-2xl md:text-[1.8rem] text-fg mt-3 leading-snug" style="font-weight:500;">
                        {{ $conv->title }}
                    </h2>
                    @if($conv->short_description)
                        <p class="mt-2.5 text-muted leading-relaxed text-sm md:text-base">
                            {{ $conv->short_description }}
                        </p>
                    @endif
                </div>

                {{-- Datos clave: área / modalidad / cierre (bien espaciados) --}}
                @if($conv->area || $conv->modality || $closes)
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-y border-border py-5">
                    @if($conv->area)
                    <div>
                        <dt class="text-[10px] tracking-[0.16em] uppercase text-muted font-semibold mb-1">ÁREA</dt>
                        <dd class="text-sm font-semibold text-fg">{{ $conv->area }}</dd>
                    </div>
                    @endif
                    @if($conv->modality)
                    <div>
                        <dt class="text-[10px] tracking-[0.16em] uppercase text-muted font-semibold mb-1">MODALIDAD</dt>
                        <dd class="text-sm font-semibold text-fg">{{ $conv->modality }}</dd>
                    </div>
                    @endif
                    @if($closes)
                    <div class="sm:col-span-2">
                        <dt class="text-[10px] tracking-[0.16em] uppercase text-muted font-semibold mb-1">FECHA DE CIERRE</dt>
                        <dd class="text-sm font-semibold text-fg font-mono num-tabular">
                            {{ $closes->translatedFormat('d \\d\\e F \\d\\e Y · H:i') }}
                        </dd>
                    </div>
                    @endif
                </dl>
                @endif

                {{-- Cronograma (si existe) --}}
                @if(count($cronograma))
                <div>
                    <p class="text-[10px] tracking-[0.16em] uppercase text-muted font-semibold mb-3">CRONOGRAMA</p>
                    <ol class="space-y-2.5">
                        @foreach($cronograma as $idx => $item)
                        @php $isLast = $idx === count($cronograma)-1; @endphp
                        <li class="flex items-start gap-3 relative {{ $isLast ? '' : 'pb-2.5' }}">
                            @if(!$isLast)<div class="absolute left-[11px] top-6 bottom-0 w-px bg-border"></div>@endif
                            <div class="relative z-10 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5"
                                 style="background:rgb(var(--color-soft)/0.7); color:rgb(var(--color-navy)); border:1.5px solid rgb(var(--color-soft));">
                                {{ $idx+1 }}
                            </div>
                            <div class="flex-1 flex items-baseline justify-between gap-2 min-w-0">
                                <span class="text-sm font-medium text-fg">{{ $item['etapa'] ?? '' }}</span>
                                @if(!empty($item['fecha']))
                                <span class="text-xs text-muted font-mono flex-shrink-0">
                                    {{ \Carbon\Carbon::parse($item['fecha'])->translatedFormat('d M Y') }}
                                    @if(!empty($item['hora'])) · {{ $item['hora'] }}@endif
                                </span>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </div>
                @endif

                {{-- Requisitos mínimos --}}
                @if(count($requirements))
                <div>
                    <p class="text-[10px] tracking-[0.16em] uppercase text-muted font-semibold mb-3">REQUISITOS MÍNIMOS</p>
                    <ul class="grid sm:grid-cols-2 gap-x-6 gap-y-2">
                        @foreach($requirements as $req)
                        <li class="flex items-start gap-2 text-sm text-fg/90">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                 viewBox="0 0 24 24" style="color:rgb(var(--color-accent));">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                            {{ $req }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Botón ver detalles completos (página interna) --}}
                <a href="{{ $convLink }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors self-start mt-auto"
                   style="background:rgb(var(--color-navy));color:#fff;"
                   onmouseover="this.style.background='rgb(var(--color-primary))'"
                   onmouseout="this.style.background='rgb(var(--color-navy))'">
                    Ver convocatoria completa y documentos
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>

            {{-- ── Columna derecha: countdown + documentos ─────────────────────── --}}
            <div class="flex flex-col gap-4 md:sticky md:top-6">

                {{-- Countdown --}}
                <div class="rounded-2xl px-7 py-6 md:px-8 md:py-7 relative overflow-hidden"
                     style="background:rgb(var(--color-navy));">
                    <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full opacity-[0.07]"
                         style="background:rgb(var(--color-accent));"></div>
                    @if($isOpen && $closes && $closes->isFuture())
                        <p class="text-[11px] tracking-[0.18em] uppercase font-bold mb-4 relative z-10"
                           style="color:rgba(255,255,255,0.5);">TIEMPO RESTANTE</p>
                        <div class="grid grid-cols-4 gap-2 relative z-10"
                             x-data="countdown('{{ $closes->toIso8601String() }}')"
                             x-init="start()">
                            @foreach(['days' => 'DÍAS', 'hours' => 'HRS', 'minutes' => 'MIN', 'seconds' => 'SEG'] as $k => $l)
                            <div class="text-center transition-transform duration-150"
                                 :class="{{ "flash.{$k}" }} ? 'scale-110' : ''">
                                <div class="rounded-xl py-3 mb-2"
                                     style="background:rgba(255,255,255,0.09); border:1px solid rgba(255,255,255,0.14);">
                                    <span class="font-serif text-[1.75rem] text-white num-tabular block"
                                          x-text="String({{ $k }}).padStart(2,'0')"
                                          style="font-weight:400; line-height:1.1;">00</span>
                                </div>
                                <span class="block text-[10px] tracking-[0.16em] uppercase font-semibold"
                                      style="color:rgba(255,255,255,0.6);">{{ $l }}</span>
                            </div>
                            @endforeach
                        </div>
                        <p class="mt-4 text-xs relative z-10 text-center"
                           style="color:rgba(255,255,255,0.38);">
                            Cierre: {{ $closes->translatedFormat('d \\d\\e F \\d\\e Y · H:i') }}
                        </p>
                    @else
                        <div class="text-center relative z-10 py-2">
                            <svg class="w-8 h-8 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" style="color:rgba(255,255,255,0.35);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                            </svg>
                            <p class="text-xs font-semibold tracking-widest uppercase" style="color:rgba(255,255,255,0.45);">Proceso cerrado</p>
                            @if($closes)
                            <p class="font-serif text-lg text-white/60 mt-1.5" style="font-weight:400;">
                                {{ $closes->translatedFormat('d \\d\\e F · H:i') }}
                            </p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Documentos --}}
                @if($docTotal > 0)
                <div class="bg-card border border-border rounded-2xl overflow-hidden" x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left transition-colors"
                            :class="open ? 'bg-brand-soft/15' : 'hover:bg-brand-soft/10'">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                 style="background:rgb(var(--color-soft)/0.5);">
                                <svg style="width:15px;height:15px;color:rgb(var(--color-navy));"
                                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-fg">Documentos del proceso</p>
                                <p class="text-xs text-muted">{{ $docTotal }} archivo{{ $docTotal !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-muted transition-transform duration-200 flex-shrink-0"
                             :class="open ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         style="display:none;">
                        <div class="border-t border-border divide-y divide-border/50">
                            @if($conv->bases_pdf)
                            @php $info = \App\Models\Convocatoria::fileTypeInfo($conv->bases_pdf); @endphp
                            <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-brand-soft/5 transition-colors">
                                <span class="w-9 h-9 rounded-lg flex items-center justify-center text-[10px] font-bold border flex-shrink-0 {{ $info['bg'] }} {{ $info['text'] }}">{{ $info['label'] }}</span>
                                <span class="flex-1 text-sm font-medium text-fg truncate">Bases del proceso</span>
                                <a href="{{ Storage::disk('public')->url($conv->bases_pdf) }}" target="_blank" download
                                   class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors"
                                   style="background:rgb(var(--color-soft)/0.5);color:rgb(var(--color-navy));"
                                   onmouseover="this.style.background='rgb(var(--color-soft))'"
                                   onmouseout="this.style.background='rgb(var(--color-soft)/0.5)'">⬇ Descargar</a>
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
                            <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-brand-soft/5 transition-colors">
                                <span class="w-9 h-9 rounded-lg flex items-center justify-center text-[10px] font-bold border flex-shrink-0 {{ $info['bg'] }} {{ $info['text'] }}">{{ $info['label'] }}</span>
                                <span class="flex-1 text-sm font-medium text-fg truncate" title="{{ $nombre }}">{{ $nombre }}</span>
                                <a href="{{ $url }}" target="_blank" download
                                   class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors"
                                   style="background:rgb(var(--color-soft)/0.5);color:rgb(var(--color-navy));"
                                   onmouseover="this.style.background='rgb(var(--color-soft))'"
                                   onmouseout="this.style.background='rgb(var(--color-soft)/0.5)'">⬇ Descargar</a>
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
<section class="bg-brand-soft/20">
    <div class="section-wrap">
        <div class="max-w-4xl mx-auto rounded-2xl overflow-hidden shadow-md border border-border"
             data-aos="fade-up" data-aos-duration="700">

            {{-- Header navy --}}
            <div class="relative px-8 md:px-10 pt-8 pb-10 overflow-hidden"
                 style="background:rgb(var(--color-navy));">
                <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full opacity-[0.06]"
                     style="background:rgb(var(--color-accent));" aria-hidden="true"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-start md:justify-between gap-5">
                    <div class="flex-1 min-w-0">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full mb-4"
                              style="{{ $isOpen ? 'background:rgba(52,211,153,0.15);color:#6ee7b7;border:1px solid rgba(52,211,153,0.3);' : 'background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.5);border:1px solid rgba(255,255,255,0.15);' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isOpen ? 'bg-emerald-400 animate-pulse' : 'bg-white/30' }}"></span>
                            {{ $isOpen ? 'Convocatoria abierta' : 'Convocatoria cerrada' }}
                        </span>
                        <h2 class="font-serif text-2xl md:text-3xl text-white leading-snug" style="font-weight:500;">{{ $conv->title }}</h2>
                        @if($conv->short_description)
                        <p class="mt-2.5 text-sm leading-relaxed" style="color:rgba(255,255,255,0.6);">{{ $conv->short_description }}</p>
                        @endif
                    </div>
                    @if($isOpen && $closes && $closes->isFuture())
                    <div class="flex-shrink-0" x-data="countdown('{{ $closes->toIso8601String() }}')" x-init="start()">
                        <p class="text-[10px] tracking-widest uppercase font-bold mb-2 text-center"
                           style="color:rgba(255,255,255,0.4);">TIEMPO RESTANTE</p>
                        <div class="flex items-end gap-1">
                            @foreach(['days' => 'DÍAS', 'hours' => 'HRS', 'minutes' => 'MIN', 'seconds' => 'SEG'] as $k => $l)
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-1.5"
                                     style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);">
                                    <span class="font-serif text-xl text-white num-tabular"
                                          x-text="String({{ $k }}).padStart(2,'0')" style="font-weight:400;">00</span>
                                </div>
                                <span class="block text-[9px] tracking-widest uppercase font-semibold"
                                      style="color:rgba(255,255,255,0.55);">{{ $l }}</span>
                            </div>
                            @if($l !== 'SEG')
                            <span class="text-white/25 text-lg mb-4 mx-0.5">:</span>
                            @endif
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
                    <div class="px-7 py-5">
                        <dt class="text-[10px] tracking-[0.16em] uppercase text-muted font-semibold mb-1.5">ÁREA</dt>
                        <dd class="text-sm font-semibold text-fg">{{ $conv->area }}</dd>
                    </div>
                    @endif
                    @if($conv->modality)
                    <div class="px-7 py-5">
                        <dt class="text-[10px] tracking-[0.16em] uppercase text-muted font-semibold mb-1.5">MODALIDAD</dt>
                        <dd class="text-sm font-semibold text-fg">{{ $conv->modality }}</dd>
                    </div>
                    @endif
                    @if($closes)
                    <div class="px-7 py-5">
                        <dt class="text-[10px] tracking-[0.16em] uppercase text-muted font-semibold mb-1.5">FECHA DE CIERRE</dt>
                        <dd class="text-sm font-semibold text-fg font-mono num-tabular text-xs leading-relaxed">{{ $closes->translatedFormat('d \\d\\e F \\d\\e Y · H:i') }}</dd>
                    </div>
                    @endif
                </dl>
                @endif
                {{-- Cronograma --}}
                @if(count($cronograma))
                <div class="px-7 md:px-10 py-6">
                    <p class="text-[10px] tracking-[0.16em] uppercase text-muted font-semibold mb-4">CRONOGRAMA</p>
                    <ol class="space-y-2">
                        @foreach($cronograma as $idx => $item)
                        <li class="flex items-center justify-between gap-3 text-sm py-1.5 border-b border-border/60 last:border-0">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold flex-shrink-0"
                                      style="background:rgb(var(--color-soft)/0.6);color:rgb(var(--color-navy));">{{ $idx+1 }}</span>
                                <span class="font-medium text-fg">{{ $item['etapa'] ?? '' }}</span>
                            </span>
                            @if(!empty($item['fecha']))
                            <span class="text-xs text-muted font-mono flex-shrink-0">{{ \Carbon\Carbon::parse($item['fecha'])->translatedFormat('d M Y') }}@if(!empty($item['hora'])) · {{ $item['hora'] }}@endif</span>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                </div>
                @endif
                {{-- Requisitos --}}
                @if(count($requirements))
                <div class="px-7 md:px-10 py-6">
                    <p class="text-[10px] tracking-[0.16em] uppercase text-muted font-semibold mb-3">REQUISITOS MÍNIMOS</p>
                    <ul class="grid sm:grid-cols-2 gap-2">
                        @foreach($requirements as $req)
                        <li class="flex items-start gap-2 text-sm text-fg/90">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:rgb(var(--color-accent));"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            {{ $req }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                {{-- Documentos --}}
                @if($docTotal > 0)
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between gap-3 px-7 md:px-10 py-5 text-left transition-colors"
                            :class="open ? 'bg-brand-soft/15' : 'hover:bg-brand-soft/10'">
                        <span class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgb(var(--color-soft)/0.5);">
                                <svg style="width:15px;height:15px;color:rgb(var(--color-navy));" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32"/></svg>
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-fg">Documentos del proceso</span>
                                <span class="block text-xs text-muted">{{ $docTotal }} archivo{{ $docTotal !== 1 ? 's' : '' }}</span>
                            </span>
                        </span>
                        <svg class="w-4 h-4 text-muted transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150" style="display:none;">
                        <div class="border-t border-border divide-y divide-border/50">
                            @if($conv->bases_pdf)
                            @php $info = \App\Models\Convocatoria::fileTypeInfo($conv->bases_pdf); @endphp
                            <div class="flex items-center gap-3 px-7 md:px-10 py-3.5 hover:bg-brand-soft/5 transition-colors">
                                <span class="w-9 h-9 rounded-lg flex items-center justify-center text-[10px] font-bold border {{ $info['bg'] }} {{ $info['text'] }}">{{ $info['label'] }}</span>
                                <span class="flex-1 text-sm font-medium text-fg">Bases del proceso</span>
                                <a href="{{ Storage::disk('public')->url($conv->bases_pdf) }}" target="_blank" download class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors" style="background:rgb(var(--color-soft)/0.5);color:rgb(var(--color-navy));" onmouseover="this.style.background='rgb(var(--color-soft))'" onmouseout="this.style.background='rgb(var(--color-soft)/0.5)'">⬇ Descargar</a>
                            </div>
                            @endif
                            @foreach($documentos as $doc)
                            @php $archivo=$doc['archivo']??$doc['path']??''; $nombre=$doc['nombre']??basename($archivo); $info=\App\Models\Convocatoria::fileTypeInfo($archivo); $url=$archivo?Storage::disk('public')->url($archivo):'#'; @endphp
                            @if($archivo)
                            <div class="flex items-center gap-3 px-7 md:px-10 py-3.5 hover:bg-brand-soft/5 transition-colors">
                                <span class="w-9 h-9 rounded-lg flex items-center justify-center text-[10px] font-bold border {{ $info['bg'] }} {{ $info['text'] }}">{{ $info['label'] }}</span>
                                <span class="flex-1 text-sm font-medium text-fg truncate" title="{{ $nombre }}">{{ $nombre }}</span>
                                <a href="{{ $url }}" target="_blank" download class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors" style="background:rgb(var(--color-soft)/0.5);color:rgb(var(--color-navy));" onmouseover="this.style.background='rgb(var(--color-soft))'" onmouseout="this.style.background='rgb(var(--color-soft)/0.5)'">⬇ Descargar</a>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                {{-- Footer: enlace interno --}}
                <div class="px-7 md:px-10 py-4 border-t border-border flex items-center justify-between gap-4"
                     style="background:rgb(var(--color-soft)/0.1);">
                    @if($logo)
                    <img src="{{ $logo }}" alt="{{ settings('site_name','AAG') }}" class="h-7 object-contain opacity-60">
                    @endif
                    <a href="{{ $convLink }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold ml-auto transition-all px-4 py-2 rounded-lg"
                       style="color:rgb(var(--color-navy));background:rgb(var(--color-soft)/0.5);"
                       onmouseover="this.style.background='rgb(var(--color-soft))'"
                       onmouseout="this.style.background='rgb(var(--color-soft)/0.5)'">
                        Ver convocatoria completa
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════ LAYOUT: MINIMAL (borde izquierdo, limpio) ════════════════════════ --}}
@else
<section class="bg-bg">
    <div class="section-wrap">
        <div class="max-w-3xl" data-aos="fade-up" data-aos-duration="700">
            <div class="relative pl-8">
                <div class="absolute left-0 top-0 bottom-0 w-1 rounded-full"
                     style="background:linear-gradient(to bottom, rgb(var(--color-navy)), rgb(var(--color-accent)));"></div>

                <span class="text-[10px] tracking-[0.2em] uppercase font-bold"
                      style="color:rgb(var(--color-accent));">PROCESO DE CONTRATACIÓN</span>

                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mt-3">
                    <div>
                        <h2 class="font-serif text-2xl md:text-3xl text-fg leading-snug" style="font-weight:500;">{{ $conv->title }}</h2>
                        @if($conv->short_description)
                        <p class="mt-2 text-muted leading-relaxed">{{ $conv->short_description }}</p>
                        @endif
                    </div>
                    @if($isOpen && $closes && $closes->isFuture())
                    <div class="flex-shrink-0 rounded-xl px-4 py-3 text-center border border-border bg-brand-soft/10"
                         x-data="countdown('{{ $closes->toIso8601String() }}')" x-init="start()">
                        <p class="text-[9px] tracking-widest uppercase text-muted font-semibold mb-1.5">TIEMPO RESTANTE</p>
                        <div class="flex items-center gap-1 justify-center">
                            @foreach(['days' => 'D', 'hours' => 'H', 'minutes' => 'M', 'seconds' => 'S'] as $k => $l)
                            <div class="text-center">
                                <span class="font-serif text-xl num-tabular block" style="color:rgb(var(--color-navy));font-weight:400;"
                                      x-text="String({{ $k }}).padStart(2,'0')">00</span>
                                <span class="text-[8px] text-muted">{{ $l }}</span>
                            </div>
                            @if($l !== 'S')<span class="text-muted text-base mx-0.5 mb-3">:</span>@endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Meta chips --}}
                @if($conv->area || $conv->modality || $closes)
                <div class="flex flex-wrap gap-2 mt-5">
                    @if($conv->area)
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full border border-border bg-card text-fg">
                        <svg class="w-3.5 h-3.5 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        {{ $conv->area }}
                    </span>
                    @endif
                    @if($conv->modality)
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full border border-border bg-card text-fg">
                        {{ $conv->modality }}
                    </span>
                    @endif
                    @if($closes)
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full border border-border bg-card text-fg font-mono num-tabular">
                        <svg class="w-3.5 h-3.5 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25"/></svg>
                        Cierre: {{ $closes->translatedFormat('d M Y · H:i') }}
                    </span>
                    @endif
                </div>
                @endif

                {{-- Documentos accordion mínimal --}}
                @if($docTotal > 0)
                <div class="mt-5 border border-border rounded-xl overflow-hidden" x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between gap-3 px-5 py-3.5 text-left text-sm font-semibold text-fg hover:bg-brand-soft/10 transition-colors">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32"/></svg>
                            Documentos ({{ $docTotal }})
                        </span>
                        <svg class="w-4 h-4 text-muted transition-transform" :class="open ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <div x-show="open" x-transition style="display:none;">
                        <div class="border-t border-border divide-y divide-border/50">
                            @if($conv->bases_pdf)
                            @php $info = \App\Models\Convocatoria::fileTypeInfo($conv->bases_pdf); @endphp
                            <div class="flex items-center gap-3 px-5 py-3 hover:bg-brand-soft/5 transition-colors">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-[10px] font-bold border {{ $info['bg'] }} {{ $info['text'] }}">{{ $info['label'] }}</span>
                                <span class="flex-1 text-sm text-fg">Bases del proceso</span>
                                <a href="{{ Storage::disk('public')->url($conv->bases_pdf) }}" target="_blank" download class="text-xs font-semibold text-muted hover:text-fg transition-colors">⬇</a>
                            </div>
                            @endif
                            @foreach($documentos as $doc)
                            @php $archivo=$doc['archivo']??$doc['path']??''; $nombre=$doc['nombre']??basename($archivo); $info=\App\Models\Convocatoria::fileTypeInfo($archivo); $url=$archivo?Storage::disk('public')->url($archivo):'#'; @endphp
                            @if($archivo)
                            <div class="flex items-center gap-3 px-5 py-3 hover:bg-brand-soft/5 transition-colors">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-[10px] font-bold border {{ $info['bg'] }} {{ $info['text'] }}">{{ $info['label'] }}</span>
                                <span class="flex-1 text-sm text-fg truncate" title="{{ $nombre }}">{{ $nombre }}</span>
                                <a href="{{ $url }}" target="_blank" download class="text-xs font-semibold text-muted hover:text-fg transition-colors">⬇</a>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-5 flex items-center gap-3">
                    @if($logo)
                    <img src="{{ $logo }}" alt="{{ settings('site_name','AAG') }}" class="h-6 object-contain opacity-50">
                    @endif
                    <a href="{{ $convLink }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold transition-colors"
                       style="color:rgb(var(--color-primary));"
                       onmouseover="this.style.color='rgb(var(--color-navy))'"
                       onmouseout="this.style.color='rgb(var(--color-primary))'">
                        Ver convocatoria completa y documentos
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif{{-- /procesoLayout --}}
@endif{{-- /tipo --}}
