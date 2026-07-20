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

<x-ui.breadcrumb-bar :items="[
    ['label' => 'Convocatorias', 'url' => route('convocatorias.index')],
    ['label' => $conv->title, 'url' => null],
]" />

<x-ui.page-header :title="$conv->title" :description="$conv->short_description" data-aos="fade-up">
    <x-slot:meta>
        {{-- Estado diferenciado por color: el ciudadano debe ver si el proceso
             sigue abierto sin tener que leer el texto del chip. --}}
        <span class="{{ $isOpen ? 'chip-abierto' : 'chip-cerrado' }}">
            {{ $isOpen ? 'Convocatoria abierta' : 'Convocatoria cerrada' }}
        </span>
        <span class="pill">{{ $conv->tipo === 'aviso' ? 'Aviso institucional' : 'Proceso de contratación' }}</span>
    </x-slot:meta>
</x-ui.page-header>

<div class="bg-bg">
    <div class="section-wrap">
        <div class="grid lg:grid-cols-[1fr_380px] gap-8 lg:gap-10 items-start">

            {{-- ══ COLUMNA PRINCIPAL ═══════════════════════════════════════════ --}}
            <div class="min-w-0">

                {{-- Datos clave (área, modalidad, cierre) --}}
                @if($conv->area || $conv->modality || $closes)
                <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 card-surface p-5"
                    data-aos="fade-up" data-aos-delay="80">
                    @if($conv->area)
                    <div>
                        <dt class="kicker">Área</dt>
                        <dd class="mt-1.5 text-[15px] font-semibold text-fg">{{ $conv->area }}</dd>
                    </div>
                    @endif
                    @if($conv->modality)
                    <div>
                        <dt class="kicker">Modalidad</dt>
                        <dd class="mt-1.5 text-[15px] font-semibold text-fg">{{ $conv->modality }}</dd>
                    </div>
                    @endif
                    @if($closes)
                    <div class="{{ !$conv->area && !$conv->modality ? '' : 'col-span-2 md:col-span-1' }}">
                        <dt class="kicker">Fecha de cierre</dt>
                        <dd class="mt-1.5 text-[15px] font-semibold text-fg num-tabular">
                            <time datetime="{{ $closes->toIso8601String() }}">
                                {{ $closes->translatedFormat('d \\d\\e F \\d\\e Y') }}
                                <span class="block font-normal text-muted">{{ $closes->format('H:i') }}</span>
                            </time>
                        </dd>
                    </div>
                    @endif
                </dl>
                @endif

                {{-- Cronograma --}}
                @if(count($cronograma))
                <div class="mt-8" data-aos="fade-up">
                    <h2 class="font-serif text-lg uppercase text-brand-navy rule-accent pb-2.5 mb-5">Cronograma del proceso</h2>
                    <ol class="relative border-l border-border ml-2 space-y-0">
                        @foreach($cronograma as $idx => $item)
                        @php $isLast = $idx === count($cronograma) - 1; @endphp
                        <li class="relative pl-7 {{ $isLast ? 'pb-0' : 'pb-6' }}">
                            {{-- Marcador cuadrado (2px): en B no hay circulos.
                                 Sube de 18 a 22px con el numero a 12px: a 9px la
                                 cifra dentro del cuadrado no se leia. --}}
                            <span class="absolute -left-[11px] top-0 w-[22px] h-[22px] rounded-pill flex items-center justify-center bg-brand-navy text-[12px] font-bold text-white num-tabular">
                                {{ $idx + 1 }}
                            </span>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">
                                <p class="text-[15px] font-semibold text-fg leading-snug">{{ $item['etapa'] ?? '' }}</p>
                                @if(!empty($item['fecha']))
                                <span class="pill num-tabular w-fit">
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
                    <h2 class="font-serif text-lg uppercase text-brand-navy rule-accent pb-2.5 mb-5">Requisitos mínimos</h2>
                    <ul class="grid sm:grid-cols-2 gap-3">
                        @foreach($requirements as $req)
                        <li class="flex items-start gap-3 rounded-card border border-border bg-card p-4 text-[15px] leading-[1.45] text-fg">
                            <svg class="w-4 h-4 flex-shrink-0 mt-1 text-brand-accent" fill="none" stroke="currentColor" aria-hidden="true"
                                 stroke-width="2.5" viewBox="0 0 24 24">
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
                <div class="rounded-card rule-accent bg-brand-navy p-5" data-aos="fade-left">
                    {{-- Logo en header del countdown --}}
                    @if($logo)
                    <div class="mb-4 pb-3 border-b border-white/15">
                        <img src="{{ $logo }}" alt="{{ $siteName }}"
                             class="h-7 object-contain brightness-0 invert opacity-70">
                    </div>
                    @endif

                    <p class="kicker text-white/70 mb-3">Tiempo restante</p>
                    <div class="grid grid-cols-4 gap-2"
                         x-data="countdown('{{ $closes->toIso8601String() }}')"
                         x-init="start()">
                        @foreach(['days' => 'DÍAS', 'hours' => 'HRS', 'minutes' => 'MIN', 'seconds' => 'SEG'] as $k => $l)
                        <div class="text-center">
                            <div class="rounded-pill py-2.5 mb-1.5 bg-white/10 border border-white/15">
                                <span class="block font-serif text-[1.6rem] leading-none text-white num-tabular"
                                      x-text="String({{ $k }}).padStart(2,'0')">00</span>
                            </div>
                            {{-- 12px y opacidad mas alta: DIAS/HRS/MIN/SEG son la
                                 clave de lectura del contador y a 10px sobre
                                 navy con un 60% de blanco casi desaparecian. --}}
                            <span class="block text-[12px] tracking-[0.08em] uppercase font-bold text-white/75">{{ $l }}</span>
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-4 text-[12px] text-center text-white/60 num-tabular">
                        Cierre: {{ $closes->translatedFormat('d \\d\\e F \\d\\e Y · H:i') }}
                    </p>
                </div>
                @elseif(!$isOpen && $closes)
                <div class="card-surface p-5 text-center" data-aos="fade-left">
                    <svg class="w-8 h-8 mx-auto text-muted/50 mb-2" fill="none" stroke="currentColor"
                         stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                    </svg>
                    <p class="kicker text-muted">Proceso cerrado</p>
                    <p class="mt-2 text-[12px] uppercase tracking-[0.07em] font-bold text-muted">Cerro el</p>
                    <p class="mt-1 font-serif text-lg uppercase text-brand-navy num-tabular">
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
                <a href="{{ route('convocatorias.index') }}" class="btn-ghost w-full">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Ver todas las convocatorias
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
