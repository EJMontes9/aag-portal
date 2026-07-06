@extends('layouts.app', [
    'title'       => $metaTitle,
    'description' => $metaDesc,
])

@push('json-ld')
<script type="application/ld+json">
{!! json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'GovernmentService',
    'name'       => $conv->title,
    'description' => $conv->short_description ?: $metaDesc,
    'url'        => url()->current(),
    'provider'   => ['@id' => url('/') . '#organization'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
    $logo     = setting_asset('site_logo');
    $siteName = settings('site_name', 'Autoridad Aeroportuaria de Guayaquil');
@endphp

{{-- Breadcrumbs --}}
<div class="border-b border-border bg-bg">
    <div class="section-wrap !py-4">
        <x-layout.breadcrumbs :items="[
            ['label' => 'Convocatorias', 'url' => route('convocatorias.index')],
            ['label' => $conv->title, 'url' => null],
        ]" />
    </div>
</div>

<div class="bg-bg">
    <div class="section-wrap">
        <div class="grid lg:grid-cols-[1fr_380px] gap-8 lg:gap-10 items-start">

            {{-- ══ COLUMNA PRINCIPAL ═══════════════════════════════════════════ --}}
            <div class="min-w-0">

                {{-- Cabecera: logo + estado + título --}}
                <div class="flex items-start gap-4 mb-6">
                    @if($logo)
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl flex items-center justify-center border border-border bg-card p-2 shadow-sm">
                        <img src="{{ $logo }}" alt="{{ $siteName }}" class="w-full h-full object-contain">
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border
                                  {{ $isOpen
                                      ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                                      : 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isOpen ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                {{ $isOpen ? 'Convocatoria abierta' : 'Convocatoria cerrada' }}
                            </span>
                            <span class="text-xs text-muted">
                                {{ $conv->tipo === 'aviso' ? 'Aviso institucional' : 'Proceso de contratación' }}
                            </span>
                        </div>
                        @if($logo)
                        <p class="text-xs text-muted font-medium">{{ $siteName }}</p>
                        @endif
                    </div>
                </div>

                <h1 class="font-serif text-3xl md:text-4xl text-fg leading-tight" style="font-weight:500;"
                    data-aos="fade-up">
                    {{ $conv->title }}
                </h1>

                @if($conv->short_description)
                <p class="mt-4 text-muted text-lg leading-relaxed" data-aos="fade-up" data-aos-delay="50">
                    {{ $conv->short_description }}
                </p>
                @endif

                {{-- Datos clave (área, modalidad, cierre) --}}
                @if($conv->area || $conv->modality || $closes)
                <dl class="mt-6 grid grid-cols-2 md:grid-cols-3 gap-4 p-5 rounded-xl border border-border bg-card"
                    data-aos="fade-up" data-aos-delay="80">
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
                    <div class="{{ !$conv->area && !$conv->modality ? '' : 'col-span-2 md:col-span-1' }}">
                        <dt class="text-[10px] tracking-[0.16em] uppercase text-muted font-semibold mb-1">FECHA DE CIERRE</dt>
                        <dd class="text-sm font-semibold text-fg font-mono num-tabular">
                            {{ $closes->translatedFormat('d \\d\\e F \\d\\e Y') }}<br>
                            <span class="text-muted font-sans font-normal">{{ $closes->format('H:i') }}</span>
                        </dd>
                    </div>
                    @endif
                </dl>
                @endif

                {{-- Cronograma --}}
                @if(count($cronograma))
                <div class="mt-8" data-aos="fade-up">
                    <h2 class="text-[11px] tracking-[0.18em] uppercase font-bold text-muted mb-5">CRONOGRAMA DEL PROCESO</h2>
                    <ol class="relative border-l-2 border-border ml-3 space-y-0">
                        @foreach($cronograma as $idx => $item)
                        @php $isLast = $idx === count($cronograma) - 1; @endphp
                        <li class="relative pl-6 {{ $isLast ? 'pb-0' : 'pb-6' }}">
                            {{-- Punto de la línea --}}
                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full flex items-center justify-center"
                                 style="background:rgb(var(--color-navy)); border:2px solid rgb(var(--color-soft));">
                                <span class="text-[8px] text-white font-bold">{{ $idx + 1 }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                <p class="font-semibold text-fg text-sm">{{ $item['etapa'] ?? '' }}</p>
                                @if(!empty($item['fecha']))
                                <span class="text-xs text-muted font-mono bg-brand-soft/30 px-2.5 py-1 rounded-full w-fit">
                                    {{ \Carbon\Carbon::parse($item['fecha'])->translatedFormat('d \\d\\e F \\d\\e Y') }}
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
                <div class="mt-8" data-aos="fade-up">
                    <h2 class="text-[11px] tracking-[0.18em] uppercase font-bold text-muted mb-4">REQUISITOS MÍNIMOS</h2>
                    <ul class="grid sm:grid-cols-2 gap-3">
                        @foreach($requirements as $req)
                        <li class="flex items-start gap-3 p-3.5 rounded-xl border border-border bg-card text-sm text-fg">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                 stroke-width="2.5" viewBox="0 0 24 24" style="color:rgb(var(--color-accent));">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                            {{ $req }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- DOCUMENTOS — sección principal en mobile (abajo del texto en desktop se repite en sidebar) --}}
                @if($docTotal > 0)
                <div class="mt-8 lg:hidden">
                    @include('pages.convocatoria._documents', ['conv' => $conv, 'documentos' => $documentos, 'docTotal' => $docTotal])
                </div>
                @endif

            </div>

            {{-- ══ SIDEBAR DERECHO ══════════════════════════════════════════════ --}}
            <div class="flex flex-col gap-5 lg:sticky lg:top-8">

                {{-- Countdown --}}
                @if($isOpen && $closes && $closes->isFuture())
                <div class="rounded-2xl p-6 relative overflow-hidden"
                     style="background:rgb(var(--color-navy));" data-aos="fade-left">
                    <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full opacity-[0.07]"
                         style="background:rgb(var(--color-accent));" aria-hidden="true"></div>

                    {{-- Logo en header del countdown --}}
                    @if($logo)
                    <div class="relative z-10 mb-5 pb-4" style="border-bottom:1px solid rgba(255,255,255,0.12);">
                        <img src="{{ $logo }}" alt="{{ $siteName }}"
                             class="h-8 object-contain brightness-0 invert opacity-70">
                    </div>
                    @endif

                    <p class="text-[11px] tracking-[0.18em] uppercase font-bold relative z-10 mb-3"
                       style="color:rgba(255,255,255,0.5);">TIEMPO RESTANTE</p>
                    <div class="grid grid-cols-4 gap-2 relative z-10"
                         x-data="countdown('{{ $closes->toIso8601String() }}')"
                         x-init="start()">
                        @foreach(['days' => 'DÍAS', 'hours' => 'HRS', 'minutes' => 'MIN', 'seconds' => 'SEG'] as $k => $l)
                        <div class="text-center transition-transform duration-150"
                             :class="{{ "flash.{$k}" }} ? 'scale-110' : ''">
                            <div class="rounded-xl py-3 mb-2"
                                 style="background:rgba(255,255,255,0.09);border:1px solid rgba(255,255,255,0.14);">
                                <span class="font-serif text-[1.6rem] text-white num-tabular block"
                                      x-text="String({{ $k }}).padStart(2,'0')"
                                      style="font-weight:400;line-height:1.1;">00</span>
                            </div>
                            <span class="block text-[10px] tracking-[0.14em] uppercase font-semibold"
                                  style="color:rgba(255,255,255,0.6);">{{ $l }}</span>
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-4 text-xs relative z-10 text-center"
                       style="color:rgba(255,255,255,0.38);">
                        Cierre: {{ $closes->translatedFormat('d \\d\\e F \\d\\e Y · H:i') }}
                    </p>
                </div>
                @elseif(!$isOpen && $closes)
                <div class="rounded-2xl p-5 border border-border bg-card text-center" data-aos="fade-left">
                    <svg class="w-8 h-8 mx-auto text-muted/50 mb-2" fill="none" stroke="currentColor"
                         stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                    </svg>
                    <p class="text-xs font-semibold text-muted uppercase tracking-widest">Proceso cerrado</p>
                    <p class="font-serif text-lg text-fg mt-1" style="font-weight:400;">
                        {{ $closes->translatedFormat('d \\d\\e F \\d\\e Y') }}
                    </p>
                </div>
                @endif

                {{-- Documentos descargables (sidebar, desktop) --}}
                @if($docTotal > 0)
                <div class="hidden lg:block" data-aos="fade-left" data-aos-delay="80">
                    @include('pages.convocatoria._documents', ['conv' => $conv, 'documentos' => $documentos, 'docTotal' => $docTotal])
                </div>
                @endif

                {{-- Volver al listado --}}
                <a href="{{ route('convocatorias.index') }}"
                   class="flex items-center gap-2 text-sm font-medium text-muted hover:text-fg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Ver todas las convocatorias
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
