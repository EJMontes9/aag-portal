@extends('layouts.app', [
    'title'    => $project->meta_title ?: $project->title,
    'description' => $project->meta_description ?: $project->summary,
    'ogImage'  => $project->cover_url,
])

@push('json-ld')
@php
    $siteName = settings('site_name', 'Autoridad Aeroportuaria de Guayaquil');
@endphp
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context'    => 'https://schema.org',
    '@type'       => 'Project',
    'name'        => $project->title,
    'description' => $project->meta_description ?: $project->summary,
    'url'         => url()->current(),
    'image'       => $project->cover_url ?: null,
    'startDate'   => $project->start_date?->toDateString(),
    'endDate'     => $project->end_date?->toDateString(),
    'location'    => $project->location ? ['@type' => 'Place', 'name' => $project->location] : null,
    'funder'      => ['@type' => 'GovernmentOrganization', '@id' => url('/') . '#organization'],
], fn($v) => $v !== null && $v !== ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

{{-- El BreadcrumbList lo emite <x-ui.breadcrumb-bar>, no se duplica aqui. --}}

@section('content')
<article class="bg-bg">
    <x-ui.breadcrumb-bar :items="[
        ['label' => 'Proyectos', 'url' => route('projects.index')],
        ['label' => $project->title, 'url' => null],
    ]" />

    <x-ui.page-header :title="$project->title" :description="$project->summary">
        <x-slot:meta>
            {{-- Clases literales por rama: Tailwind no compila clases armadas en runtime --}}
            <span class="{{ match($project->status) {
                'en_curso'   => 'chip-proceso',
                'completado' => 'chip-abierto',
                default      => 'chip-cerrado',
            } }}">{{ $project->status_label }}</span>
            @if($project->location)
                <span class="text-[13px] text-muted inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    {{ $project->location }}
                </span>
            @endif
        </x-slot:meta>
    </x-ui.page-header>

    {{-- Imagen principal --}}
    @if($project->cover_url)
        <div class="section-wrap !pb-0">
            <figure class="aspect-[16/9] rounded-card overflow-hidden bg-cloud-gradient">
                <img src="{{ $project->cover_url }}"
                     alt="{{ $project->title }}"
                     class="w-full h-full object-cover">
            </figure>
        </div>
    @endif

    <div class="section-wrap">
        <div class="grid lg:grid-cols-[1fr_320px] gap-10 max-w-6xl mx-auto">

            {{-- Cuerpo --}}
            <div>
                @if($project->description)
                    {{-- Mismos valores de lectura larga que el detalle de noticia
                         (16px / 1.75) y misma medida acotada: la columna del
                         proyecto llega a ~860px en escritorio y sin este tope la
                         linea se iba muy por encima de los 75 caracteres. --}}
                    <div class="prose max-w-[72ch]
                                prose-headings:font-serif prose-headings:uppercase prose-headings:text-brand-navy
                                prose-h2:text-[20px] prose-h2:mt-9 prose-h2:mb-3
                                prose-h3:text-[17px] prose-h3:mt-7 prose-h3:mb-2
                                prose-p:text-[16px] prose-p:text-fg prose-p:leading-[1.75] prose-p:my-[1.15em]
                                prose-li:text-[16px] prose-li:leading-[1.7] prose-li:my-1
                                prose-a:text-brand-primary prose-a:no-underline hover:prose-a:underline
                                prose-strong:text-fg prose-strong:font-semibold
                                prose-blockquote:border-brand-accent prose-blockquote:text-fg
                                prose-img:rounded-card">
                        {!! $project->description !!}
                    </div>
                @endif

                {{-- Galeria con lightbox Alpine --}}
                @php $gallery = collect($project->gallery ?? []); @endphp
                @if($gallery->isNotEmpty())
                    <section class="mt-10">
                        <h2 class="font-serif text-lg uppercase text-brand-navy rule-accent pb-2.5 mb-5">Galeria</h2>

                        <div x-data="{
                                open: false,
                                idx: 0,
                                images: @js($gallery->map(fn ($g) => \Illuminate\Support\Facades\Storage::disk('public')->url($g))->values()->all()),
                                show(i) { this.idx = i; this.open = true; document.body.style.overflow = 'hidden'; },
                                close() { this.open = false; document.body.style.overflow = ''; },
                                next() { this.idx = (this.idx + 1) % this.images.length; },
                                prev() { this.idx = (this.idx - 1 + this.images.length) % this.images.length; },
                             }"
                             @keydown.escape.window="if (open) close()"
                             @keydown.arrow-right.window="if (open) next()"
                             @keydown.arrow-left.window="if (open) prev()">

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-[18px]">
                                @foreach($gallery as $i => $g)
                                    <button type="button"
                                            @click="show({{ $i }})"
                                            aria-label="Ampliar imagen {{ $i + 1 }} de {{ $gallery->count() }}"
                                            class="block aspect-square overflow-hidden rounded-card border border-border bg-cloud-gradient transition-colors hover:border-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($g) }}"
                                             alt="Imagen {{ $i + 1 }} de {{ $project->title }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover">
                                    </button>
                                @endforeach
                            </div>

                            {{-- Lightbox --}}
                            <div x-show="open" x-cloak x-transition.opacity
                                 class="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center"
                                 @click.self="close()">
                                <button type="button" @click="close()" aria-label="Cerrar"
                                        class="absolute top-5 right-5 w-10 h-10 rounded-pill bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-black">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <button type="button" @click="prev()" x-show="images.length > 1" aria-label="Anterior"
                                        class="absolute left-5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-pill bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-black">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                                </button>
                                <button type="button" @click="next()" x-show="images.length > 1" aria-label="Siguiente"
                                        class="absolute right-5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-pill bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-black">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                </button>
                                <div class="max-w-[90vw] max-h-[85vh]" @click.stop>
                                    <img :src="images[idx]" :alt="'Imagen ' + (idx + 1)" class="max-w-full max-h-[80vh] object-contain rounded-card">
                                    <p class="mt-2.5 text-white/70 text-[12px] text-center num-tabular">
                                        <span x-text="idx + 1"></span> / <span x-text="images.length"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Hitos --}}
                @php $milestones = collect($project->milestones ?? [])->filter(fn ($m) => !empty($m['label'])); @endphp
                @if($milestones->isNotEmpty())
                    <section class="mt-10">
                        <h2 class="font-serif text-lg uppercase text-brand-navy rule-accent pb-2.5 mb-5">Cronograma</h2>
                        <ol class="relative border-l border-border ml-2 space-y-5 pl-6">
                            @foreach($milestones as $m)
                                <li class="relative">
                                    {{-- Marcador cuadrado de 2px: en B no hay circulos.
                                         Relleno celeste = hito cumplido. --}}
                                    <span class="absolute -left-[1.8rem] top-1 w-3 h-3 rounded-pill {{ ($m['completed'] ?? false) ? 'bg-brand-primary' : 'bg-card border border-border' }}"></span>
                                    @if(!empty($m['date']))
                                        {{-- La fecha es dato de consulta, no adorno: 12px
                                             y en bloque, para que no se pegue al hito. --}}
                                        <time datetime="{{ \Carbon\Carbon::parse($m['date'])->toDateString() }}"
                                              class="block text-[12px] uppercase tracking-[0.08em] text-muted font-bold num-tabular">
                                            {{ \Carbon\Carbon::parse($m['date'])->translatedFormat('d \\d\\e F, Y') }}
                                        </time>
                                    @endif
                                    <p class="mt-1 text-[15px] leading-snug font-semibold {{ ($m['completed'] ?? false) ? 'text-fg' : 'text-muted' }}">
                                        {{ $m['label'] }}
                                        @if($m['completed'] ?? false)
                                            <span class="chip-abierto ml-2">Completado</span>
                                        @endif
                                    </p>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif
            </div>

            {{-- Ficha lateral --}}
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="card-surface p-5">
                    <h2 class="kicker block pb-3 rule-accent">Ficha del proyecto</h2>
                    <dl class="mt-4 space-y-4 text-[15px]">
                        <div>
                            {{-- Los rotulos de la ficha suben de 10 a 12px: son
                                 mayusculas condensadas con tracking, el caso en
                                 el que 10px mas castiga la lectura. --}}
                            <dt class="text-muted text-[12px] tracking-[0.08em] uppercase font-bold">Estado</dt>
                            <dd class="text-fg font-semibold mt-0.5">{{ $project->status_label }}</dd>
                        </div>
                        @if($project->budget)
                            <div>
                                <dt class="text-muted text-[12px] tracking-[0.08em] uppercase font-bold">Presupuesto</dt>
                                <dd class="text-fg font-semibold mt-0.5 num-tabular">{{ $project->budget }}</dd>
                            </div>
                        @endif
                        @if($project->location)
                            <div>
                                <dt class="text-muted text-[12px] tracking-[0.08em] uppercase font-bold">Ubicacion</dt>
                                <dd class="text-fg font-semibold mt-0.5">{{ $project->location }}</dd>
                            </div>
                        @endif
                        @if($project->start_date)
                            <div>
                                <dt class="text-muted text-[12px] tracking-[0.08em] uppercase font-bold">Inicio</dt>
                                <dd class="text-fg font-semibold mt-0.5">{{ $project->start_date->translatedFormat('d \\d\\e F \\d\\e Y') }}</dd>
                            </div>
                        @endif
                        @if($project->end_date)
                            <div>
                                <dt class="text-muted text-[12px] tracking-[0.08em] uppercase font-bold">Finalizacion</dt>
                                <dd class="text-fg font-semibold mt-0.5">{{ $project->end_date->translatedFormat('d \\d\\e F \\d\\e Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </aside>
        </div>

        {{-- Relacionados --}}
        @if($related->isNotEmpty())
            <div class="max-w-6xl mx-auto mt-12">
                <h2 class="font-serif text-lg uppercase text-brand-navy rule-accent pb-2.5 mb-5">Otros proyectos {{ strtolower($project->status_label) }}s</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-[18px]">
                    @foreach($related as $r)
                        <article class="group flex flex-col card-surface overflow-hidden transition-colors duration-200 hover:border-brand-primary">
                            <a href="{{ route('projects.show', $r->slug) }}" class="block aspect-[16/10] overflow-hidden bg-cloud-gradient"
                               tabindex="-1" aria-hidden="true">
                                @if($r->cover_url)
                                    <img src="{{ $r->cover_url }}" alt="{{ $r->title }}" loading="lazy"
                                         class="w-full h-full object-cover">
                                @endif
                            </a>
                            <h3 class="p-[18px] font-sans font-semibold text-[15px] text-fg leading-[1.3]">
                                <a href="{{ route('projects.show', $r->slug) }}"
                                   class="rounded-pill transition-colors group-hover:text-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
                                    {{ $r->title }}
                                </a>
                            </h3>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</article>
@endsection
