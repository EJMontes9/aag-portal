@extends('layouts.app', [
    'title'       => 'Convocatorias',
    'description' => 'Procesos de selección y avisos institucionales de la Autoridad Aeroportuaria de Guayaquil.',
])

@push('json-ld')
<script type="application/ld+json">
{!! json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio',         'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Convocatorias'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
@php
    $logo     = setting_asset('site_logo');
    $siteName = settings('site_name', 'Autoridad Aeroportuaria de Guayaquil');
@endphp

<div class="bg-bg">
    <div class="section-wrap">

        {{-- Breadcrumbs --}}
        <x-layout.breadcrumbs :items="[['label' => 'Convocatorias', 'url' => null]]" class="mb-8" />

        {{-- Encabezado --}}
        <header class="max-w-2xl mb-10" data-aos="fade-up">
            <span class="text-[11px] tracking-[0.18em] uppercase text-muted font-semibold">PROCESOS Y AVISOS</span>
            <h1 class="font-serif text-4xl md:text-5xl text-fg mt-3" style="font-weight:500;">Convocatorias</h1>
            <p class="mt-3 text-muted leading-relaxed">
                Procesos de selección de proveedores, consultores y personal, así como avisos institucionales
                de la {{ $siteName }}.
            </p>
        </header>

        {{-- Convocatorias vigentes --}}
        @if($vigentes->isNotEmpty())
        <section class="mb-12">
            <h2 class="text-[11px] tracking-[0.18em] uppercase font-bold text-muted mb-5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                Vigentes
            </h2>
            <div class="grid md:grid-cols-2 gap-4">
                @foreach($vigentes as $conv)
                @php
                    $docTotal = count((array)($conv->documentos ?? [])) + ($conv->bases_pdf ? 1 : 0);
                @endphp
                <a href="{{ route('convocatorias.show', $conv->slug) }}"
                   class="group flex flex-col bg-card border border-border rounded-2xl p-6 hover:border-brand-primary/40 hover:shadow-md transition-all duration-200"
                   data-aos="fade-up">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold border bg-emerald-50 text-emerald-800 border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Vigente
                        </span>
                        <span class="text-xs text-muted bg-brand-soft/30 px-2.5 py-1 rounded-full">
                            {{ $conv->tipo === 'aviso' ? 'Aviso' : 'Proceso' }}
                        </span>
                    </div>

                    <h3 class="font-serif text-lg text-fg leading-snug group-hover:text-brand-navy transition-colors" style="font-weight:500;">
                        {{ $conv->title }}
                    </h3>

                    @if($conv->short_description)
                    <p class="mt-2 text-sm text-muted leading-relaxed line-clamp-2">{{ $conv->short_description }}</p>
                    @endif

                    <div class="mt-auto pt-4 flex flex-wrap items-center gap-3 text-xs text-muted">
                        @if($conv->area)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                            {{ $conv->area }}
                        </span>
                        @endif
                        @if($conv->closes_at)
                        <span class="flex items-center gap-1 font-mono">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Cierra {{ $conv->closes_at->diffForHumans() }}
                        </span>
                        @endif
                        @if($docTotal > 0)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32"/></svg>
                            {{ $docTotal }} doc{{ $docTotal !== 1 ? 's' : '' }}
                        </span>
                        @endif
                        <span class="ml-auto flex items-center gap-1 font-semibold transition-colors"
                              style="color:rgb(var(--color-primary));">
                            Ver detalles
                            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @else
        <div class="mb-12 text-center py-16 rounded-2xl border-2 border-dashed border-border">
            <svg class="w-10 h-10 mx-auto text-muted/40 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <p class="font-serif text-xl text-fg/60" style="font-weight:400;">No hay convocatorias vigentes en este momento</p>
        </div>
        @endif

        {{-- Convocatorias cerradas --}}
        @if($cerradas->isNotEmpty())
        <section>
            <h2 class="text-[11px] tracking-[0.18em] uppercase font-bold text-muted mb-5">Procesos anteriores</h2>
            <div class="space-y-2">
                @foreach($cerradas as $conv)
                <a href="{{ route('convocatorias.show', $conv->slug) }}"
                   class="group flex items-center gap-4 p-4 rounded-xl border border-border bg-card hover:bg-brand-soft/10 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-fg/80 truncate group-hover:text-fg transition-colors">{{ $conv->title }}</p>
                        @if($conv->area)
                        <p class="text-xs text-muted mt-0.5">{{ $conv->area }}</p>
                        @endif
                    </div>
                    @if($conv->closes_at)
                    <span class="text-xs text-muted font-mono flex-shrink-0 hidden sm:block">
                        {{ $conv->closes_at->translatedFormat('d M Y') }}
                    </span>
                    @endif
                    <svg class="w-4 h-4 text-muted/50 flex-shrink-0 transition-transform group-hover:translate-x-0.5"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
                @endforeach
            </div>
        </section>
        @endif

    </div>
</div>
@endsection
