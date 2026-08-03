@extends('layouts.app', [
    'title' => 'Preguntas frecuentes',
    'description' => 'Resuelve tus dudas sobre el aeropuerto, viajes, trámites y servicios de la Autoridad Aeroportuaria de Guayaquil.',
])

{{-- ══ JSON-LD: FAQPage ══════════════════════════════════════════════════════════
     Google muestra las preguntas desplegables directamente en el SERP.
     Solo incluimos FAQs cuando no hay filtro activo (para evitar duplicados).
     El BreadcrumbList NO va aquí: lo emite <x-ui.breadcrumb-bar> más abajo.
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
{!! json_ld([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'url'        => route('faq.index'),
    'name'       => 'Preguntas frecuentes — Autoridad Aeroportuaria de Guayaquil',
    'mainEntity' => $faqItems,
]) !!}
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
    description="Encuentra respuestas rápidas sobre el aeropuerto, viajes, trámites institucionales y servicios."
    data-aos="fade-up" />

<section class="bg-bg">
    <div class="section-wrap">
        {{-- Filtros + búsqueda --}}
        {{-- Barra de filtros idéntica a la de noticias (mismos tamaños, mismo
             aria-current, mismo anillo de foco): las dos secciones ofrecen la
             misma función y no deben verse ni comportarse distinto. --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <nav class="flex flex-wrap gap-2" aria-label="Filtrar por categoría">
                <a href="{{ route('faq.index') }}" wire:navigate
                   @if(! $activeCategory) aria-current="page" @endif
                   class="pill {{ ! $activeCategory ? 'bg-brand-navy text-on-navy' : 'hover:bg-brand-soft/70 hover:text-brand-primary' }} transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                    Todas
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('faq.index', ['categoria' => $cat->slug]) }}" wire:navigate
                       @if($activeCategory === $cat->slug) aria-current="page" @endif
                       class="pill {{ $activeCategory === $cat->slug ? 'bg-brand-navy text-on-navy' : 'hover:bg-brand-soft/70 hover:text-brand-primary' }} transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('faq.index') }}" wire:navigate class="flex items-center gap-3 w-full lg:w-auto">
                @if($activeCategory)
                    <input type="hidden" name="categoria" value="{{ $activeCategory }}">
                @endif
                <label for="faq-q" class="sr-only">Buscar pregunta</label>
                <div class="relative flex-1 lg:flex-none">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                    <input type="search"
                           id="faq-q"
                           name="q"
                           value="{{ $q ?? '' }}"
                           placeholder="Buscar pregunta..."
                           class="pl-10 pr-4 py-2.5 rounded-pill border border-border bg-card text-[15px] focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary w-full lg:w-72">
                </div>
                @if($q)
                    <a href="{{ route('faq.index', ['categoria' => $activeCategory]) }}" wire:navigate
                       class="shrink-0 rounded-pill text-[12px] uppercase font-bold tracking-[0.07em] text-muted hover:text-brand-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">Limpiar</a>
                @endif
            </form>
        </div>

        {{-- Acordeón agrupado por categoría --}}
        @if($faqs->isEmpty())
            {{-- El vacío de una FAQ es el peor momento para dejar al usuario
                 solo: se le da la salida (quitar filtro) y el canal de contacto,
                 que es justo lo que venía a buscar. --}}
            <div class="mt-10 px-5 text-center py-16 rounded-card border border-dashed border-border bg-card">
                <svg class="w-10 h-10 mx-auto text-muted/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/>
                </svg>
                <p class="mt-4 font-serif text-page-title uppercase text-brand-navy">No encontramos respuestas</p>
                <p class="mt-3 mx-auto max-w-[60ch] text-[15px] leading-[1.6] text-muted">
                    @if($q)
                        No hay resultados para <strong class="font-semibold text-fg">&ldquo;{{ $q }}&rdquo;</strong>. Prueba con otra palabra, revisa todas las preguntas o escríbenos directamente.
                    @else
                        Aún no hay preguntas publicadas en esta categoría. Si tu duda es urgente, puedes escribirnos.
                    @endif
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    @if($q || $activeCategory)
                        <a href="{{ route('faq.index') }}" wire:navigate class="btn-ghost">Ver todas las preguntas</a>
                    @endif
                    <a href="/contacto" wire:navigate class="btn-primary">Ir a contacto</a>
                </div>
            </div>
        @else
            <div class="mt-8 max-w-4xl">
                @foreach($grouped as $categoryId => $items)
                    @php
                        $cat = $items->first()->category;
                    @endphp

                    @if($cat)
                        {{-- Rótulo de sección de B: 18px Neulis en mayúsculas, con el
                             filete amarillo debajo haciendo de separador. --}}
                        <h2 class="font-serif text-lg uppercase text-brand-navy rule-accent pb-2.5 mt-10 first:mt-0 mb-5">
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
                                        {{-- El anillo va por dentro (ring-inset): la caja
                                             del acordeón está pegada a la siguiente y un
                                             anillo con offset se solaparía con ella. --}}
                                        class="w-full flex items-start justify-between gap-4 text-left px-4 py-4 transition-colors hover:bg-brand-soft/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-primary">
                                    <span class="text-[16px] font-bold leading-[1.35] text-brand-navy">{{ $faq->question }}</span>
                                    {{-- El indicador de B es un CARÁCTER (− / +), no un icono:
                                         el acordeón no gira ni anima nada, solo cambia el signo. --}}
                                    <span aria-hidden="true"
                                          class="shrink-0 w-4 text-center text-[20px] font-bold leading-[1.35] text-brand-primary"
                                          x-text="openIdx === '{{ $idx }}' ? '−' : '+'">+</span>
                                </button>
                                <div x-show="openIdx === '{{ $idx }}'"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     id="faq-panel-{{ $idx }}"
                                     style="display: none;">
                                    {{-- La respuesta es texto de lectura: sube de 13 a
                                         15px con interlineado 1.7 y se le da aire
                                         arriba (pt-4) para despegarla del filete. --}}
                                    <div class="px-4 pb-5 pt-4 border-t border-border prose max-w-[70ch]
                                                prose-p:text-[15px] prose-p:text-fg prose-p:leading-[1.7] prose-p:my-3
                                                prose-li:text-[15px] prose-li:leading-[1.6]
                                                prose-a:text-brand-primary prose-a:no-underline hover:prose-a:underline
                                                prose-strong:text-fg prose-strong:font-semibold
                                                prose-ul:my-3 prose-li:my-1">
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
        {{-- El CTA solo tiene sentido cuando SÍ hay respuestas listadas: en el
             estado vacío ya se ofrece el mismo botón de contacto y repetirlo
             dejaba dos llamadas idénticas a un palmo una de otra. --}}
        @if($faqs->isNotEmpty())
        <div class="mt-12 max-w-3xl card-surface rule-accent p-8 md:p-10 text-center">
            <span class="kicker">¿No encontraste tu respuesta?</span>
            <h2 class="font-serif text-section-title uppercase text-brand-navy mt-2.5">Conversemos directamente</h2>
            <p class="mt-3 mx-auto max-w-[60ch] text-[15px] leading-[1.6] text-muted">Nuestro equipo responde consultas, sugerencias y solicitudes de información pública.</p>
            <a href="/contacto" wire:navigate class="btn-primary mt-6">Ir a contacto</a>
        </div>
        @endif
    </div>
</section>
@endsection
