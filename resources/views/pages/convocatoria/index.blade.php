@extends('layouts.app', [
    'title'       => 'Convocatorias',
    'description' => 'Procesos de selección y avisos institucionales de la Autoridad Aeroportuaria de Guayaquil.',
])

{{-- El BreadcrumbList lo emite <x-ui.breadcrumb-bar>, no se duplica aqui. --}}

@section('content')
@php
    $logo     = setting_asset('site_logo');
    $siteName = settings('site_name', 'Autoridad Aeroportuaria de Guayaquil');
@endphp

<x-ui.breadcrumb-bar :items="[
    ['label' => 'Convocatorias', 'url' => null],
]" />

<x-ui.page-header
    kicker="Procesos y avisos"
    title="Convocatorias"
    :description="'Procesos de selección de proveedores, consultores y personal, así como avisos institucionales de la ' . $siteName . '.'"
    data-aos="fade-up" />

<div class="bg-bg">
    <div class="section-wrap">

        {{-- Convocatorias vigentes --}}
        @if($vigentes->isNotEmpty())
        <section>
            <h2 class="font-serif text-lg uppercase text-brand-navy rule-accent pb-2.5 mb-5">Vigentes</h2>

            {{-- Filas de listado de B (misma caja que .b-list: blanca, borde
                 marcado, padding 20px, hover solo de borde). Se escriben las
                 utilidades a mano en vez de usar .b-list porque esa clase ya
                 trae "flex gap-5" para el thumb lateral, y aqui el contenido va
                 apilado: una convocatoria no tiene portada y un placeholder de
                 160x110 repetido en cada fila solo anadiria ruido. --}}
            <div class="flex flex-col gap-3">
                @foreach($vigentes as $conv)
                @php
                    $docTotal = count((array)($conv->documentos ?? [])) + ($conv->bases_pdf ? 1 : 0);
                @endphp
                <a href="{{ route('convocatorias.show', $conv->slug) }}"
                   class="group block rounded-card border border-border bg-card p-5 transition-colors duration-200 hover:border-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg"
                   data-aos="fade-up">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="chip-abierto">Vigente</span>
                        <span class="pill">{{ $conv->tipo === 'aviso' ? 'Aviso' : 'Proceso' }}</span>
                    </div>

                    {{-- Misma jerarquia que la fila de noticias: titulo 18px,
                         resumen 14px, metadatos 12px. --}}
                    <h3 class="mt-2.5 font-serif text-[18px] leading-[1.25] text-brand-navy transition-colors group-hover:text-brand-primary">
                        {{ $conv->title }}
                    </h3>

                    @if($conv->short_description)
                    <p class="mt-2 text-[14px] leading-[1.6] text-muted line-clamp-2">{{ $conv->short_description }}</p>
                    @endif

                    <div class="mt-3.5 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[12px] text-muted">
                        @if($conv->area)
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                            {{ $conv->area }}
                        </span>
                        @endif
                        @if($conv->closes_at)
                        <span class="inline-flex items-center gap-1.5 num-tabular">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <time datetime="{{ $conv->closes_at->toIso8601String() }}">Cierra {{ $conv->closes_at->diffForHumans() }}</time>
                        </span>
                        @endif
                        @if($docTotal > 0)
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32"/></svg>
                            {{ $docTotal }} doc{{ $docTotal !== 1 ? 's' : '' }}
                        </span>
                        @endif
                        {{-- sm:ml-auto y no ml-auto: por debajo de 640px la fila de
                             metadatos ya va a dos lineas y empujar este rotulo a
                             la derecha lo dejaba solo y descolgado. --}}
                        <span class="w-full sm:w-auto sm:ml-auto text-[12px] font-bold uppercase tracking-[0.07em] text-brand-primary">
                            Ver detalles &rsaquo;
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @else
        {{-- El vacio aqui es un estado normal (no siempre hay procesos abiertos):
             se explica que significa y se orienta a donde mirar, en vez de
             dejar solo el titular negativo. --}}
        <div class="px-5 text-center py-16 rounded-card border border-dashed border-border bg-card">
            <svg class="w-10 h-10 mx-auto text-muted/60 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <p class="font-serif text-page-title uppercase text-brand-navy">No hay convocatorias vigentes</p>
            <p class="mt-3 mx-auto max-w-[60ch] text-[15px] leading-[1.6] text-muted">
                En este momento no hay procesos ni avisos abiertos. Los nuevos se publican en esta misma pagina@if($cerradas->isNotEmpty()); mas abajo puedes consultar los procesos anteriores@endif.
            </p>
        </div>
        @endif

        {{-- Convocatorias cerradas --}}
        @if($cerradas->isNotEmpty())
        <section class="mt-10">
            <h2 class="font-serif text-lg uppercase text-brand-navy rule-accent pb-2.5 mb-5">Procesos anteriores</h2>
            <div class="flex flex-col gap-2">
                @foreach($cerradas as $conv)
                <a href="{{ route('convocatorias.show', $conv->slug) }}"
                   class="group flex items-center gap-4 rounded-card border border-border bg-card px-4 py-3.5 transition-colors hover:border-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                    <span class="chip-cerrado shrink-0">Cerrado</span>
                    <span class="flex-1 min-w-0">
                        <span class="block text-[15px] font-semibold text-fg truncate transition-colors group-hover:text-brand-primary">{{ $conv->title }}</span>
                        @if($conv->area)
                        <span class="block mt-0.5 text-[12px] text-muted truncate">{{ $conv->area }}</span>
                        @endif
                    </span>
                    @if($conv->closes_at)
                    <time datetime="{{ $conv->closes_at->toIso8601String() }}"
                          class="hidden sm:block shrink-0 text-[12px] text-muted num-tabular">
                        {{ $conv->closes_at->translatedFormat('d M Y') }}
                    </time>
                    @endif
                </a>
                @endforeach
            </div>
        </section>
        @endif

    </div>
</div>
@endsection
