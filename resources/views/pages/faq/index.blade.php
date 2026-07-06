@extends('layouts.app', [
    'title' => 'Preguntas frecuentes',
    'description' => 'Resuelve tus dudas sobre el aeropuerto, viajes, tramites y servicios de la Autoridad Aeroportuaria de Guayaquil.',
])

{{-- ══ JSON-LD: FAQPage ══════════════════════════════════════════════════════════
     Google muestra las preguntas desplegables directamente en el SERP.
     Solo incluimos FAQs cuando no hay filtro activo (para evitar duplicados).
─────────────────────────────────────────────────────────────────────────────── --}}
@if(!$activeCategory && !$q && $faqs->isNotEmpty())
@push('json-ld')
@php
    $faqItems = $faqs->take(20)->map(fn($f) => [   // Google muestra hasta 20
        '@type'          => 'Question',
        'name'           => strip_tags($f->question),
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text'  => Str::limit(strip_tags($f->answer), 1500),
        ],
    ])->values()->all();
@endphp
<script type="application/ld+json">
{!! json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'url'        => route('faq.index'),
    'name'       => 'Preguntas frecuentes — Autoridad Aeroportuaria de Guayaquil',
    'mainEntity' => $faqItems,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

{{-- BreadcrumbList --}}
<script type="application/ld+json">
{!! json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Preguntas frecuentes'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
@endif

@section('content')
<section class="bg-bg">
    <div class="section-wrap">
        {{-- Breadcrumbs --}}
        <x-layout.breadcrumbs :items="[
            ['label' => 'Preguntas frecuentes', 'url' => null]
        ]" />

        {{-- Encabezado --}}
        <header class="max-w-3xl mt-6" data-aos="fade-up">
            <span class="font-sans text-[11px] tracking-[0.18em] uppercase text-muted font-semibold">CENTRO DE AYUDA</span>
            <h1 class="font-serif text-section-title text-fg mt-3">Preguntas frecuentes</h1>
            <p class="mt-4 text-muted leading-[1.65] max-w-2xl">
                Encuentra respuestas rapidas sobre el aeropuerto, viajes, tramites institucionales y servicios.
            </p>
        </header>

        {{-- Filtros + busqueda --}}
        <div class="mt-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <nav class="flex flex-wrap gap-2" aria-label="Filtrar por categoria">
                <a href="{{ route('faq.index') }}"
                   class="pill {{ ! $activeCategory ? 'bg-brand-navy text-on-navy' : 'bg-brand-soft/40 text-fg hover:bg-brand-soft/70' }} transition-colors">
                    Todas
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('faq.index', ['categoria' => $cat->slug]) }}"
                       class="pill {{ $activeCategory === $cat->slug ? 'bg-brand-navy text-on-navy' : 'bg-brand-soft/40 text-fg hover:bg-brand-soft/70' }} transition-colors">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('faq.index') }}" class="flex items-center gap-2">
                @if($activeCategory)
                    <input type="hidden" name="categoria" value="{{ $activeCategory }}">
                @endif
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                    <input type="search"
                           name="q"
                           value="{{ $q ?? '' }}"
                           placeholder="Buscar pregunta..."
                           class="pl-9 pr-4 py-2 rounded-full border border-border bg-card text-sm focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary w-full sm:w-72">
                </div>
                @if($q)
                    <a href="{{ route('faq.index', ['categoria' => $activeCategory]) }}"
                       class="text-sm text-muted hover:text-fg transition-colors">Limpiar</a>
                @endif
            </form>
        </div>

        {{-- Acordeon agrupado por categoria --}}
        @if($faqs->isEmpty())
            <div class="mt-16 text-center py-20 border-2 border-dashed border-border rounded-hero">
                <svg class="w-12 h-12 mx-auto text-muted/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/>
                </svg>
                <p class="mt-4 font-serif text-2xl text-fg" style="font-weight:400;">No encontramos respuestas</p>
                <p class="mt-2 text-muted">@if($q) No hay resultados para "{{ $q }}". @else Aun no hay preguntas en esta categoria. @endif</p>
            </div>
        @else
            <div class="mt-12 max-w-4xl">
                @foreach($grouped as $categoryId => $items)
                    @php
                        $cat = $items->first()->category;
                    @endphp

                    @if($cat)
                        <h2 class="font-serif text-2xl text-fg mt-12 first:mt-0 mb-6 flex items-center gap-3" style="font-weight:400;">
                            <span class="inline-flex w-8 h-8 rounded-md bg-brand-soft/50 text-brand-navy items-center justify-center text-xs font-semibold">
                                {{ strtoupper(substr($cat->name, 0, 2)) }}
                            </span>
                            {{ $cat->name }}
                        </h2>
                    @endif

                    <div x-data="{ openIdx: null }" class="space-y-3">
                        @foreach($items as $i => $faq)
                            @php $idx = $categoryId.'-'.$i; @endphp
                            <div class="border border-border rounded-card bg-card overflow-hidden transition-colors"
                                 :class="openIdx === '{{ $idx }}' ? 'border-brand-primary/40 bg-brand-soft/10' : ''">
                                <button type="button"
                                        @click="openIdx = openIdx === '{{ $idx }}' ? null : '{{ $idx }}'"
                                        :aria-expanded="openIdx === '{{ $idx }}'"
                                        aria-controls="faq-panel-{{ $idx }}"
                                        class="w-full flex items-center justify-between gap-4 text-left px-5 py-4 hover:bg-brand-soft/10 transition-colors">
                                    <span class="font-medium text-fg leading-snug">{{ $faq->question }}</span>
                                    <svg class="w-5 h-5 shrink-0 text-muted transition-transform duration-200"
                                         :class="openIdx === '{{ $idx }}' ? 'rotate-180 text-brand-primary' : ''"
                                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                    </svg>
                                </button>
                                <div x-show="openIdx === '{{ $idx }}'"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     id="faq-panel-{{ $idx }}"
                                     style="display: none;">
                                    <div class="px-5 pb-5 pt-1 prose prose-sm max-w-none
                                                prose-p:text-fg/85 prose-p:leading-[1.65]
                                                prose-a:text-brand-primary prose-a:no-underline hover:prose-a:underline
                                                prose-strong:text-fg
                                                prose-ul:my-2 prose-li:my-0.5">
                                        {!! $faq->answer !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        {{-- CTA contacto --}}
        <div class="mt-16 max-w-3xl mx-auto text-center bg-brand-soft/20 border border-border rounded-hero p-8 md:p-10">
            <span class="font-sans text-[11px] tracking-[0.18em] uppercase text-muted font-semibold">¿NO ENCONTRASTE TU RESPUESTA?</span>
            <h2 class="font-serif text-3xl text-fg mt-3" style="font-weight:400;">Conversemos directamente.</h2>
            <p class="mt-3 text-muted">Nuestro equipo responde consultas, sugerencias y solicitudes de informacion publica.</p>
            <a href="/contacto" class="btn-primary mt-6 inline-flex">Ir a contacto</a>
        </div>
    </div>
</section>
@endsection
