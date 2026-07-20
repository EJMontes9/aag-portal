@extends('layouts.app', [
    'title' => $q !== '' ? 'Resultados para "' . $q . '"' : 'Buscador',
    'description' => 'Busca noticias, convocatorias, proyectos, paginas y preguntas frecuentes de la Autoridad Aeroportuaria de Guayaquil.',
])

@push('head')
    {{-- Una pagina de resultados no aporta nada al indice y genera tantas URLs
         distintas como terminos se busquen. --}}
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
<x-ui.breadcrumb-bar :items="[
    ['label' => 'Buscador', 'url' => null],
]" />

<x-ui.page-header
    kicker="Buscador"
    title="Buscar en el portal"
    description="Consulta a la vez noticias, convocatorias, proyectos, paginas institucionales y preguntas frecuentes."
    data-aos="fade-up" />

<section class="bg-bg">
    <div class="section-wrap">

        {{-- ── Campo de busqueda ─────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('search') }}" role="search" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <label for="search-q" class="sr-only">Buscar en el portal</label>
            <div class="relative flex-1">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input type="search"
                       id="search-q"
                       name="q"
                       value="{{ $q }}"
                       maxlength="100"
                       autofocus
                       placeholder="Escribe lo que buscas..."
                       class="w-full pl-12 pr-4 py-3.5 rounded-card border-2 border-brand-navy bg-card text-[16px] focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary">
            </div>
            <button type="submit" class="btn-primary shrink-0 py-3.5">Buscar</button>
        </form>

        @if($q !== '')
            {{-- Contador anunciado por lectores de pantalla: el numero de
                 resultados cambia sin recargar el foco del usuario. --}}
            <p class="mt-5 text-[14px] text-muted num-tabular" aria-live="polite">
                @if($total === 0)
                    Sin resultados para <strong class="font-semibold text-fg">&ldquo;{{ $q }}&rdquo;</strong>
                @else
                    <strong class="font-semibold text-fg">{{ $total }}</strong>
                    {{ $total === 1 ? 'resultado' : 'resultados' }}
                    para <strong class="font-semibold text-fg">&ldquo;{{ $q }}&rdquo;</strong>
                    @if($resultados->lastPage() > 1)
                        <span aria-hidden="true"> · </span>
                        <span>Pagina {{ $resultados->currentPage() }} de {{ $resultados->lastPage() }}</span>
                    @endif
                @endif
            </p>
        @endif

        {{-- ── Resultados ────────────────────────────────────────────────── --}}
        @if($q !== '' && $total > 0)
            <div class="mt-8 flex flex-col gap-10">
                @foreach($grupos as $tipo => $items)
                    <section aria-labelledby="grupo-{{ $tipo }}">
                        <h2 id="grupo-{{ $tipo }}" class="kicker flex items-baseline gap-3 pb-2.5 border-b-2 border-brand-navy">
                            <span>{{ \App\Http\Controllers\SearchController::etiquetaTipo($tipo) }}</span>
                            <span class="num-tabular text-muted normal-case tracking-normal">{{ $items->count() }}</span>
                        </h2>

                        <ul class="mt-4 flex flex-col gap-3">
                            @foreach($items as $item)
                                <li>
                                    {{-- El enlace envuelve la tarjeta entera: un solo
                                         parada de tabulador por resultado, en vez de
                                         obligar a recorrer titulo y extracto por
                                         separado. --}}
                                    <a href="{{ $item['url'] }}"
                                       class="block rounded-card border-2 border-border bg-card px-5 py-4 transition-colors hover:border-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                                        <h3 class="font-serif text-[17px] leading-[1.3] text-brand-navy">
                                            {!! \App\Http\Controllers\SearchController::resaltar($item['titulo'], $q, 140) !!}
                                        </h3>

                                        @if($item['texto'] !== '')
                                            <p class="mt-2 text-[14px] leading-[1.6] text-muted line-clamp-2">
                                                {!! \App\Http\Controllers\SearchController::resaltar($item['texto'], $q) !!}
                                            </p>
                                        @endif

                                        @if($item['fecha'])
                                            <p class="mt-2 text-[12px] text-muted num-tabular">{{ $item['fecha'] }}</p>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $resultados->links() }}
            </div>

        {{-- ── Estado vacio ──────────────────────────────────────────────── --}}
        @elseif($q !== '')
            <div class="mt-8 px-5 text-center py-16 rounded-card border-2 border-dashed border-border bg-card">
                <svg class="w-10 h-10 mx-auto text-muted/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <p class="mt-4 font-serif text-page-title uppercase text-brand-navy">
                    No se encontraron resultados para &ldquo;{{ $q }}&rdquo;
                </p>
                <p class="mt-3 mx-auto max-w-[60ch] text-[15px] leading-[1.6] text-muted">
                    Revisa la ortografia, prueba con una sola palabra o usa un termino mas general.
                </p>

                <ul class="mt-6 flex flex-wrap justify-center gap-2">
                    @foreach(['aeropuerto', 'convocatoria', 'vuelos', 'proyectos', 'transparencia'] as $sugerencia)
                        <li>
                            <a href="{{ route('search', ['q' => $sugerencia]) }}"
                               class="pill hover:bg-brand-soft/70 hover:text-brand-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
                                {{ $sugerencia }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-7 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('news.index') }}" class="btn-ghost">Ver noticias</a>
                    <a href="{{ route('faq.index') }}" class="btn-ghost">Preguntas frecuentes</a>
                </div>
            </div>

        {{-- ── Sin termino todavia ───────────────────────────────────────── --}}
        @else
            <div class="mt-8 px-5 py-14 text-center rounded-card border-2 border-dashed border-border bg-card">
                <p class="font-serif text-page-title uppercase text-brand-navy">Escribe un termino para empezar</p>
                <p class="mt-3 mx-auto max-w-[60ch] text-[15px] leading-[1.6] text-muted">
                    La busqueda recorre a la vez noticias, convocatorias, proyectos, paginas institucionales y preguntas frecuentes.
                </p>
                <ul class="mt-6 flex flex-wrap justify-center gap-2">
                    @foreach(['aeropuerto', 'convocatoria', 'vuelos', 'proyectos', 'transparencia'] as $sugerencia)
                        <li>
                            <a href="{{ route('search', ['q' => $sugerencia]) }}"
                               class="pill hover:bg-brand-soft/70 hover:text-brand-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
                                {{ $sugerencia }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>
</section>
@endsection
