@extends('layouts.app', [
    'title' => 'Noticias y boletines' . ($news->currentPage() > 1 ? ' — Página ' . $news->currentPage() : ''),
    'description' => 'Noticias institucionales, comunicados y boletines de la Autoridad Aeroportuaria de Guayaquil.',
])

@push('head')
    {{-- Paginación: rel prev/next ayuda a Google a entender la serie --}}
    @if($news->currentPage() > 1)
        <link rel="prev" href="{{ $news->previousPageUrl() }}">
    @endif
    @if($news->hasMorePages())
        <link rel="next" href="{{ $news->nextPageUrl() }}">
    @endif
    {{-- Canonical apunta siempre a la URL sin parámetros en página 1 --}}
    @if($news->currentPage() === 1 && !$activeCategory && !$q)
        <link rel="canonical" href="{{ route('news.index') }}">
    @endif
@endpush

{{-- El BreadcrumbList lo emite <x-ui.breadcrumb-bar>, no se duplica aqui. --}}

@section('content')
<x-ui.breadcrumb-bar :items="[
    ['label' => 'Noticias', 'url' => null],
]" />

<x-ui.page-header
    kicker="Sala de prensa"
    title="Noticias y boletines"
    description="Comunicados oficiales, novedades operativas y actualizaciones del aeropuerto Jose Joaquin de Olmedo."
    data-aos="fade-up" />

<section class="bg-bg">
    <div class="section-wrap">
        {{-- Filtros + busqueda --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <nav class="flex flex-wrap gap-2" aria-label="Filtrar por categoria">
                <a href="{{ route('news.index') }}"
                   class="pill {{ ! $activeCategory ? 'bg-brand-navy text-on-navy' : 'hover:bg-brand-soft/70' }} transition-colors">
                    Todas
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('news.index', ['categoria' => $cat->slug]) }}"
                       class="pill {{ $activeCategory === $cat->slug ? 'bg-brand-navy text-on-navy' : 'hover:bg-brand-soft/70' }} transition-colors">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('news.index') }}" class="flex items-center gap-3">
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
                           placeholder="Buscar noticias..."
                           class="pl-9 pr-4 py-2 rounded-pill border border-border bg-card text-[13px] focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary w-full sm:w-72">
                </div>
                @if($q)
                    <a href="{{ route('news.index', ['categoria' => $activeCategory]) }}"
                       class="text-[11px] uppercase font-bold tracking-[0.07em] text-muted hover:text-brand-primary transition-colors">Limpiar</a>
                @endif
            </form>
        </div>

        {{-- Listado --}}
        @if($news->isEmpty())
            <div class="mt-10 text-center py-16 rounded-card border border-dashed border-border bg-card">
                <svg class="w-10 h-10 mx-auto text-muted/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5"/>
                </svg>
                <p class="mt-4 font-serif text-page-title uppercase text-brand-navy">No hay noticias que mostrar</p>
                <p class="mt-2 text-[13px] text-muted">@if($q) No encontramos resultados para "{{ $q }}". @else Aun no se han publicado noticias en esta categoria. @endif</p>
            </div>
        @else
            {{-- En B el listado de noticias es una PILA de filas horizontales
                 (.b-list), no una rejilla de tarjetas: la rejilla queda para
                 proyectos y para los destacados de portada. --}}
            <div class="mt-8 flex flex-col gap-4">
                @foreach($news as $item)
                    <article class="b-list group">
                        <a href="{{ route('news.show', $item->slug) }}" class="b-list-thumb" tabindex="-1" aria-hidden="true">
                            @if($item->cover_url)
                                <img src="{{ $item->cover_url }}"
                                     alt="{{ $item->cover_image_alt ?: $item->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover">
                            @else
                                {{-- Sin portada queda a la vista el gradiente navy->celeste --}}
                                <span class="w-full h-full flex items-center justify-center text-white/40">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 1 1.5 1.5Z"/>
                                    </svg>
                                </span>
                            @endif
                        </a>

                        <div class="min-w-0 flex-1">
                            @if($item->category)
                                <span class="pill mb-2"
                                      @if($item->category->color) style="color: {{ $item->category->color }};" @endif>
                                    {{ $item->category->name }}
                                </span>
                            @endif

                            <h2 class="font-serif text-base leading-snug text-brand-navy">
                                <a href="{{ route('news.show', $item->slug) }}" class="transition-colors group-hover:text-brand-primary">
                                    {{ $item->title }}
                                </a>
                            </h2>

                            <div class="mt-1.5 text-[11px] text-muted">
                                <time datetime="{{ $item->published_at?->toIso8601String() }}">
                                    {{ $item->published_at?->format('d.m.Y') }}
                                </time>
                                <span aria-hidden="true"> · </span>
                                <span>Por {{ $item->author?->name ?? 'AAG' }}</span>
                            </div>

                            @if($item->excerpt)
                                <p class="mt-2 text-xs leading-[1.6] text-muted line-clamp-2">{{ $item->excerpt }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Paginacion --}}
            <div class="mt-10">
                {{ $news->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
