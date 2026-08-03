@extends('layouts.app', [
    'title' => 'Proyectos y obras',
    'description' => 'Proyectos institucionales y obras de infraestructura de la Autoridad Aeroportuaria de Guayaquil.',
])

@section('content')
<x-ui.breadcrumb-bar :items="[
    ['label' => 'Proyectos y obras', 'url' => null],
]" />

<x-ui.page-header
    kicker="Gestión institucional"
    title="Proyectos y obras"
    description="Iniciativas y obras de infraestructura que llevamos adelante para mejorar la operación del aeropuerto y la experiencia de quienes transitan por él."
    data-aos="fade-up" />

<section class="bg-bg">
    <div class="section-wrap">
        {{-- Filtros por estado --}}
        {{-- Mismo tratamiento de chips de filtro que noticias y FAQ: aria-current
             para que el estado activo no dependa solo del color, y anillo de
             foco de teclado. --}}
        <nav class="flex flex-wrap gap-2" aria-label="Filtrar por estado">
            <a href="{{ route('projects.index') }}"
               wire:navigate
               @if(! $activeStatus) aria-current="page" @endif
               class="pill {{ ! $activeStatus ? 'bg-brand-navy text-on-navy' : 'hover:bg-brand-soft/70 hover:text-brand-primary' }} transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                Todos ({{ $counts['all'] }})
            </a>
            <a href="{{ route('projects.index', ['estado' => 'en_curso']) }}"
               wire:navigate
               @if($activeStatus === 'en_curso') aria-current="page" @endif
               class="pill {{ $activeStatus === 'en_curso' ? 'bg-brand-navy text-on-navy' : 'hover:bg-brand-soft/70 hover:text-brand-primary' }} transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                En curso ({{ $counts['en_curso'] }})
            </a>
            <a href="{{ route('projects.index', ['estado' => 'completado']) }}"
               wire:navigate
               @if($activeStatus === 'completado') aria-current="page" @endif
               class="pill {{ $activeStatus === 'completado' ? 'bg-brand-navy text-on-navy' : 'hover:bg-brand-soft/70 hover:text-brand-primary' }} transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                Completados ({{ $counts['completado'] }})
            </a>
            <a href="{{ route('projects.index', ['estado' => 'planificado']) }}"
               wire:navigate
               @if($activeStatus === 'planificado') aria-current="page" @endif
               class="pill {{ $activeStatus === 'planificado' ? 'bg-brand-navy text-on-navy' : 'hover:bg-brand-soft/70 hover:text-brand-primary' }} transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                Planificados ({{ $counts['planificado'] }})
            </a>
        </nav>

        {{-- Grid --}}
        @if($projects->isEmpty())
            <div class="mt-10 px-5 text-center py-16 rounded-card border border-dashed border-border bg-card">
                <svg class="w-10 h-10 mx-auto text-muted/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/>
                </svg>
                <p class="mt-4 font-serif text-page-title uppercase text-brand-navy">No hay proyectos en este estado</p>
                {{-- Con salida: el filtro es la causa más probable del vacío. --}}
                <p class="mt-3 mx-auto max-w-[60ch] text-[15px] leading-[1.6] text-muted">
                    @if($activeStatus)
                        Ningún proyecto se encuentra actualmente en este estado. Puedes revisar el resto de iniciativas y obras.
                    @else
                        Todavía no se han publicado proyectos. Aquí aparecerán las obras e iniciativas de infraestructura de la institución.
                    @endif
                </p>
                @if($activeStatus)
                    <a href="{{ route('projects.index') }}" wire:navigate class="btn-ghost mt-6">Ver todos los proyectos</a>
                @endif
            </div>
        @else
            {{-- Rejilla de tarjetas ".b-card": mismo patrón que
                 pages/news/partials/card.blade.php (borde marcado, sin sombra,
                 hover solo de color). --}}
            <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-[18px]">
                @foreach($projects as $p)
                    <article class="group flex flex-col card-surface overflow-hidden transition-colors duration-200 hover:border-brand-primary">
                        {{-- Mismo criterio que la tarjeta de noticia: la imagen
                             enlaza al mismo sitio que el título, así que sale
                             del recorrido de teclado para no duplicar parada. --}}
                        <a href="{{ route('projects.show', $p->slug) }}" wire:navigate class="block aspect-[16/10] overflow-hidden bg-cloud-gradient"
                           tabindex="-1" aria-hidden="true">
                            @if($p->cover_url)
                                <img src="{{ $p->cover_url }}"
                                     alt="{{ $p->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white/40">
                                    <svg class="w-11 h-11" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/>
                                    </svg>
                                </div>
                            @endif
                        </a>
                        <div class="p-[18px] flex flex-col flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-2.5">
                                {{-- Clases LITERALES en cada rama del match: una clase
                                     construida en runtime no la compila Tailwind.
                                     Lectura del color: ámbar = obra en marcha,
                                     verde = terminada, gris = todavía sin empezar. --}}
                                <span class="{{ match($p->status) {
                                    'en_curso'   => 'chip-proceso',
                                    'completado' => 'chip-abierto',
                                    default      => 'chip-cerrado',
                                } }}">{{ $p->status_label }}</span>
                                @if($p->location)
                                    <span class="text-[12px] text-muted truncate">· {{ $p->location }}</span>
                                @endif
                            </div>
                            <h3 class="font-sans font-semibold text-[15px] text-fg leading-[1.3] flex-1">
                                <a href="{{ route('projects.show', $p->slug) }}"
                                   wire:navigate
                                   class="rounded-pill transition-colors group-hover:text-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
                                    {{ $p->title }}
                                </a>
                            </h3>
                            @if($p->summary)
                                <p class="mt-2.5 text-[14px] text-muted leading-[1.6] line-clamp-3">{{ $p->summary }}</p>
                            @endif
                            @if($p->budget)
                                {{-- El presupuesto se despega con un filete: es el dato
                                     que más se consulta y antes se confundía con el
                                     final del resumen. --}}
                                <p class="mt-3.5 pt-3 border-t border-border text-[12px] text-muted num-tabular">
                                    Presupuesto: <span class="text-fg font-semibold">{{ $p->budget }}</span>
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $projects->links() }}</div>
        @endif
    </div>
</section>
@endsection
