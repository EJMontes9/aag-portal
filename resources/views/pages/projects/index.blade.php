@extends('layouts.app', [
    'title' => 'Proyectos y obras',
    'description' => 'Proyectos institucionales y obras de infraestructura de la Autoridad Aeroportuaria de Guayaquil.',
])

@section('content')
<section class="bg-bg">
    <div class="section-wrap">
        <x-layout.breadcrumbs :items="[
            ['label' => 'Proyectos y obras', 'url' => null]
        ]" />

        <header class="max-w-3xl mt-6" data-aos="fade-up">
            <span class="font-sans text-[11px] tracking-[0.18em] uppercase text-muted font-semibold">GESTION INSTITUCIONAL</span>
            <h1 class="font-serif text-section-title text-fg mt-3">Proyectos y obras</h1>
            <p class="mt-4 text-muted leading-[1.65] max-w-2xl">
                Iniciativas y obras de infraestructura que llevamos adelante para mejorar la operacion del aeropuerto y la experiencia de quienes transitan por el.
            </p>
        </header>

        {{-- Filtros por estado --}}
        <nav class="mt-10 flex flex-wrap gap-2" aria-label="Filtrar por estado">
            <a href="{{ route('projects.index') }}"
               class="pill {{ ! $activeStatus ? 'bg-brand-navy text-on-navy' : 'bg-brand-soft/40 text-fg hover:bg-brand-soft/70' }} transition-colors">
                Todos ({{ $counts['all'] }})
            </a>
            <a href="{{ route('projects.index', ['estado' => 'en_curso']) }}"
               class="pill {{ $activeStatus === 'en_curso' ? 'bg-brand-navy text-on-navy' : 'bg-brand-soft/40 text-fg hover:bg-brand-soft/70' }} transition-colors">
                En curso ({{ $counts['en_curso'] }})
            </a>
            <a href="{{ route('projects.index', ['estado' => 'completado']) }}"
               class="pill {{ $activeStatus === 'completado' ? 'bg-brand-navy text-on-navy' : 'bg-brand-soft/40 text-fg hover:bg-brand-soft/70' }} transition-colors">
                Completados ({{ $counts['completado'] }})
            </a>
            <a href="{{ route('projects.index', ['estado' => 'planificado']) }}"
               class="pill {{ $activeStatus === 'planificado' ? 'bg-brand-navy text-on-navy' : 'bg-brand-soft/40 text-fg hover:bg-brand-soft/70' }} transition-colors">
                Planificados ({{ $counts['planificado'] }})
            </a>
        </nav>

        {{-- Grid --}}
        @if($projects->isEmpty())
            <div class="mt-12 text-center py-20 border-2 border-dashed border-border rounded-hero">
                <svg class="w-12 h-12 mx-auto text-muted/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/>
                </svg>
                <p class="mt-4 font-serif text-2xl text-fg" style="font-weight:400;">No hay proyectos en esta categoria</p>
            </div>
        @else
            <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($projects as $p)
                    <article class="group flex flex-col rounded-card overflow-hidden border border-border bg-card hover:border-brand-primary/30 hover:shadow-lg transition-all">
                        <a href="{{ route('projects.show', $p->slug) }}" class="block aspect-[16/10] bg-brand-soft/30 overflow-hidden">
                            @if($p->cover_url)
                                <img src="{{ $p->cover_url }}"
                                     alt="{{ $p->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-brand-primary/40">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/>
                                    </svg>
                                </div>
                            @endif
                        </a>
                        <div class="p-5 flex flex-col flex-1">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="pill text-[10px] {{ match($p->status) {
                                    'en_curso' => 'bg-emerald-50 text-emerald-700',
                                    'completado' => 'bg-sky-50 text-sky-700',
                                    default => 'bg-amber-50 text-amber-700',
                                } }}">{{ $p->status_label }}</span>
                                @if($p->location)
                                    <span class="text-xs text-muted">· {{ $p->location }}</span>
                                @endif
                            </div>
                            <h3 class="font-serif text-xl text-fg leading-tight" style="font-weight:400;">
                                <a href="{{ route('projects.show', $p->slug) }}" class="hover:text-brand-primary transition-colors">
                                    {{ $p->title }}
                                </a>
                            </h3>
                            @if($p->summary)
                                <p class="mt-3 text-sm text-muted leading-[1.6] line-clamp-3">{{ $p->summary }}</p>
                            @endif
                            @if($p->budget)
                                <p class="mt-4 text-xs text-muted font-mono">
                                    Presupuesto: <span class="text-fg font-semibold">{{ $p->budget }}</span>
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-12">{{ $projects->links() }}</div>
        @endif
    </div>
</section>
@endsection
