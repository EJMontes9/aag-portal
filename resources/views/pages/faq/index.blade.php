@extends('layouts.app', [
    'title' => 'Preguntas frecuentes',
    'description' => 'Resuelve tus dudas sobre el aeropuerto, viajes, tramites y servicios de la Autoridad Aeroportuaria de Guayaquil.',
])

{{-- ══ JSON-LD: FAQPage ══════════════════════════════════════════════════════════
     Google muestra las preguntas desplegables directamente en el SERP.
     Solo incluimos FAQs cuando no hay filtro activo (para evitar duplicados).
     El BreadcrumbList NO va aqui: lo emite <x-ui.breadcrumb-bar> mas abajo.
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
@endpush
@endif

@section('content')
<x-ui.breadcrumb-bar :items="[
    ['label' => 'Preguntas frecuentes', 'url' => null],
]" />

<x-ui.page-header
    kicker="Centro de ayuda"
    title="Preguntas frecuentes"
    description="Encuentra respuestas rapidas sobre el aeropuerto, viajes, tramites institucionales y servicios."
    data-aos="fade-up" />

<section class="bg-bg">
    <div class="section-wrap">
        {{-- Filtros + busqueda --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <nav class="flex flex-wrap gap-2" aria-label="Filtrar por categoria">
                <a href="{{ route('faq.index') }}"
                   class="pill {{ ! $activeCategory ? 'bg-brand-navy text-on-navy' : 'hover:bg-brand-soft/70' }} transition-colors">
                    Todas
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('faq.index', ['categoria' => $cat->slug]) }}"
                       class="pill {{ $activeCategory === $cat->slug ? 'bg-brand-navy text-on-navy' : 'hover:bg-brand-soft/70' }} transition-colors">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('faq.index') }}" class="flex items-center gap-3">
                @if($activeCategory)
                    <input type="hidden" name="categoria" value="{{ $activeCategory }}">
                @endif
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                    <input type="search"
                           name="q"
                           value="{{ $q ?? '' }}"
                           placeholder="Buscar pregunta..."
                           class="pl-9 pr-4 py-2 rounded-pill border border-border bg-card text-[13px] focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary w-full sm:w-72">
                </div>
                @if($q)
                    <a href="{{ route('faq.index', ['categoria' => $activeCategory]) }}"
                       class="text-[11px] uppercase font-bold tracking-[0.07em] text-muted hover:text-brand-primary transition-colors">Limpiar</a>
                @endif
            </form>
        </div>

        {{-- Acordeon agrupado por categoria --}}
        @if($faqs->isEmpty())
            <div class="mt-10 text-center py-16 rounded-card border border-dashed border-border bg-card">
                <svg class="w-10 h-10 mx-auto text-muted/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/>
                </svg>
                <p class="mt-4 font-serif text-page-title uppercase text-brand-navy">No encontramos respuestas</p>
                <p class="mt-2 text-[13px] text-muted">@if($q) No hay resultados para "{{ $q }}". @else Aun no hay preguntas en esta categoria. @endif</p>
            </div>
        @else
            <div class="mt-8 max-w-4xl">
                @foreach($grouped as $categoryId => $items)
                    @php
                        $cat = $items->first()->category;
                    @endphp

                    @if($cat)
                        {{-- Rotulo de seccion de B: 18px Neulis en mayusculas, con el
                             filete amarillo debajo haciendo de separador. --}}
                        <h2 class="font-serif text-lg uppercase text-brand-navy rule-accent pb-2 mt-10 first:mt-0 mb-4">
                            {{ $cat->name }}
                        </h2>
                    @endif

                    <div x-data="{ openIdx: null }" class="flex flex-col gap-2">
                        @foreach($items as $i => $faq)
                            @php $idx = $categoryId.'-'.$i; @endphp
                            <div class="card-surface overflow-hidden transition-colors"
                                 :class="openIdx === '{{ $idx }}' ? 'border-brand-primary' : ''">
                                <button type="button"
                                        @click="openIdx = openIdx === '{{ $idx }}' ? null : '{{ $idx }}'"
                                        :aria-expanded="openIdx === '{{ $idx }}'"
                                        aria-controls="faq-panel-{{ $idx }}"
                                        class="w-full flex items-start justify-between gap-4 text-left px-4 py-3 transition-colors hover:bg-brand-soft/20">
                                    <span class="text-[13px] font-bold leading-snug text-brand-navy">{{ $faq->question }}</span>
                                    {{-- El indicador de B es un CARACTER (− / +), no un icono:
                                         el acordeon no gira ni anima nada, solo cambia el signo. --}}
                                    <span aria-hidden="true"
                                          class="shrink-0 w-4 text-center text-base font-bold leading-snug text-brand-primary"
                                          x-text="openIdx === '{{ $idx }}' ? '−' : '+'">+</span>
                                </button>
                                <div x-show="openIdx === '{{ $idx }}'"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     id="faq-panel-{{ $idx }}"
                                     style="display: none;">
                                    <div class="px-4 pb-4 pt-0 border-t border-border prose prose-sm max-w-none
                                                prose-p:text-[13px] prose-p:text-fg prose-p:leading-[1.65]
                                                prose-li:text-[13px]
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
        <div class="mt-12 max-w-3xl card-surface rule-accent p-8 text-center">
            <span class="kicker">¿No encontraste tu respuesta?</span>
            <h2 class="font-serif text-section-title uppercase text-brand-navy mt-2">Conversemos directamente</h2>
            <p class="mt-2.5 text-[13px] leading-[1.6] text-muted">Nuestro equipo responde consultas, sugerencias y solicitudes de informacion publica.</p>
            <a href="/contacto" class="btn-primary mt-5">Ir a contacto</a>
        </div>
    </div>
</section>
@endsection
