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

@push('json-ld')
<script type="application/ld+json">
{!! json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio',   'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Noticias'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<section class="bg-bg">
    <div class="section-wrap">
        {{-- Encabezado --}}
        <header class="max-w-3xl" data-aos="fade-up">
            <span class="font-sans text-[11px] tracking-[0.18em] uppercase text-muted font-semibold">SALA DE PRENSA</span>
            <h1 class="font-serif text-section-title text-fg mt-3">Noticias y boletines</h1>
            <p class="mt-4 text-muted leading-[1.65] max-w-2xl">
                Comunicados oficiales, novedades operativas y actualizaciones del aeropuerto Jose Joaquin de Olmedo.
            </p>
        </header>

        {{-- Filtros + busqueda --}}
        <div class="mt-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <nav class="flex flex-wrap gap-2" aria-label="Filtrar por categoria">
                <a href="{{ route('news.index') }}"
                   class="pill {{ ! $activeCategory ? 'bg-brand-navy text-on-navy' : 'bg-brand-soft/40 text-fg hover:bg-brand-soft/70' }} transition-colors">
                    Todas
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('news.index', ['categoria' => $cat->slug]) }}"
                       class="pill {{ $activeCategory === $cat->slug ? 'bg-brand-navy text-on-navy' : 'bg-brand-soft/40 text-fg hover:bg-brand-soft/70' }} transition-colors">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('news.index') }}" class="flex items-center gap-2">
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
                           placeholder="Buscar noticias..."
                           class="pl-9 pr-4 py-2 rounded-full border border-border bg-card text-sm focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary w-full sm:w-72">
                </div>
                @if($q)
                    <a href="{{ route('news.index', ['categoria' => $activeCategory]) }}"
                       class="text-sm text-muted hover:text-fg transition-colors">Limpiar</a>
                @endif
            </form>
        </div>

        {{-- Grid --}}
        @if($news->isEmpty())
            <div class="mt-16 text-center py-20 border-2 border-dashed border-border rounded-hero">
                <svg class="w-12 h-12 mx-auto text-muted/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5"/>
                </svg>
                <p class="mt-4 font-serif text-2xl text-fg" style="font-weight:400;">No hay noticias que mostrar</p>
                <p class="mt-2 text-muted">@if($q) No encontramos resultados para "{{ $q }}". @else Aun no se han publicado noticias en esta categoria. @endif</p>
            </div>
        @else
            <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($news as $item)
                    @include('pages.news.partials.card', ['item' => $item])
                @endforeach
            </div>

            {{-- Paginacion --}}
            <div class="mt-12">
                {{ $news->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
