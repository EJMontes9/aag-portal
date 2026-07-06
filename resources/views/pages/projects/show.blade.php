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

<script type="application/ld+json">
{!! json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio',    'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Proyectos', 'item' => route('projects.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $project->title],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<article class="bg-bg">
    <header class="border-b border-border">
        <div class="section-wrap pt-10 pb-8">
            <x-layout.breadcrumbs :items="[
                ['label' => 'Proyectos', 'url' => route('projects.index')],
                ['label' => $project->title, 'url' => null],
            ]" />

            <div class="max-w-3xl mt-6">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="pill {{ match($project->status) {
                        'en_curso' => 'bg-emerald-50 text-emerald-700',
                        'completado' => 'bg-sky-50 text-sky-700',
                        default => 'bg-amber-50 text-amber-700',
                    } }}">{{ $project->status_label }}</span>
                    @if($project->location)
                        <span class="text-sm text-muted inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            {{ $project->location }}
                        </span>
                    @endif
                </div>
                <h1 class="font-serif text-[2.25rem] md:text-[3rem] leading-[1.1] text-fg mt-4" style="font-weight:500;">
                    {{ $project->title }}
                </h1>
                @if($project->summary)
                    <p class="mt-5 text-lg text-muted leading-[1.55]">{{ $project->summary }}</p>
                @endif
            </div>
        </div>
    </header>

    {{-- Imagen principal --}}
    @if($project->cover_url)
        <div class="section-wrap !pt-10 !pb-0">
            <figure class="aspect-[16/9] rounded-hero overflow-hidden bg-brand-soft/30">
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
                    <div class="prose prose-lg max-w-none
                                prose-headings:font-serif prose-headings:text-fg
                                prose-p:text-fg/85 prose-p:leading-[1.75]
                                prose-a:text-brand-primary prose-a:no-underline hover:prose-a:underline
                                prose-strong:text-fg
                                prose-img:rounded-card">
                        {!! $project->description !!}
                    </div>
                @endif

                {{-- Galeria con lightbox Alpine --}}
                @php $gallery = collect($project->gallery ?? []); @endphp
                @if($gallery->isNotEmpty())
                    <section class="mt-12">
                        <h2 class="font-serif text-2xl text-fg mb-6" style="font-weight:400;">Galeria</h2>

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

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($gallery as $i => $g)
                                    <button type="button"
                                            @click="show({{ $i }})"
                                            class="group block aspect-square overflow-hidden rounded-card bg-brand-soft/30">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($g) }}"
                                             alt="Imagen {{ $i + 1 }} de {{ $project->title }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    </button>
                                @endforeach
                            </div>

                            {{-- Lightbox --}}
                            <div x-show="open" x-cloak x-transition.opacity
                                 class="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center"
                                 @click.self="close()">
                                <button type="button" @click="close()" aria-label="Cerrar"
                                        class="absolute top-5 right-5 w-11 h-11 rounded-full bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <button type="button" @click="prev()" x-show="images.length > 1"
                                        class="absolute left-5 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                                </button>
                                <button type="button" @click="next()" x-show="images.length > 1"
                                        class="absolute right-5 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                </button>
                                <div class="max-w-[90vw] max-h-[85vh]" @click.stop>
                                    <img :src="images[idx]" :alt="'Imagen ' + (idx + 1)" class="max-w-full max-h-[80vh] object-contain rounded-card">
                                    <p class="mt-2 text-white/60 text-xs text-center">
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
                    <section class="mt-12">
                        <h2 class="font-serif text-2xl text-fg mb-6" style="font-weight:400;">Cronograma</h2>
                        <ol class="relative border-l-2 border-border ml-3 space-y-6 pl-6">
                            @foreach($milestones as $m)
                                <li class="relative">
                                    <span class="absolute -left-[2.05rem] top-1 w-4 h-4 rounded-full {{ ($m['completed'] ?? false) ? 'bg-brand-primary' : 'bg-card border-2 border-border' }}"></span>
                                    @if(!empty($m['date']))
                                        <time class="text-xs uppercase tracking-wider text-muted font-semibold">
                                            {{ \Carbon\Carbon::parse($m['date'])->translatedFormat('d \\d\\e F, Y') }}
                                        </time>
                                    @endif
                                    <p class="mt-1 font-medium text-fg {{ ($m['completed'] ?? false) ? '' : 'text-fg/70' }}">
                                        {{ $m['label'] }}
                                        @if($m['completed'] ?? false)
                                            <span class="inline-flex items-center gap-1 ml-2 text-xs text-emerald-600 font-normal">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                Completado
                                            </span>
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
                <div class="bg-brand-soft/20 border border-border rounded-card p-6">
                    <h3 class="font-sans text-[11px] tracking-[0.18em] uppercase text-muted font-semibold">FICHA DEL PROYECTO</h3>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div>
                            <dt class="text-muted text-[10px] tracking-[0.14em] uppercase font-semibold">Estado</dt>
                            <dd class="text-fg font-medium mt-1">{{ $project->status_label }}</dd>
                        </div>
                        @if($project->budget)
                            <div>
                                <dt class="text-muted text-[10px] tracking-[0.14em] uppercase font-semibold">Presupuesto</dt>
                                <dd class="text-fg font-medium mt-1 font-mono">{{ $project->budget }}</dd>
                            </div>
                        @endif
                        @if($project->location)
                            <div>
                                <dt class="text-muted text-[10px] tracking-[0.14em] uppercase font-semibold">Ubicacion</dt>
                                <dd class="text-fg font-medium mt-1">{{ $project->location }}</dd>
                            </div>
                        @endif
                        @if($project->start_date)
                            <div>
                                <dt class="text-muted text-[10px] tracking-[0.14em] uppercase font-semibold">Inicio</dt>
                                <dd class="text-fg font-medium mt-1">{{ $project->start_date->translatedFormat('d \\d\\e F \\d\\e Y') }}</dd>
                            </div>
                        @endif
                        @if($project->end_date)
                            <div>
                                <dt class="text-muted text-[10px] tracking-[0.14em] uppercase font-semibold">Finalizacion</dt>
                                <dd class="text-fg font-medium mt-1">{{ $project->end_date->translatedFormat('d \\d\\e F \\d\\e Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </aside>
        </div>

        {{-- Relacionados --}}
        @if($related->isNotEmpty())
            <div class="max-w-6xl mx-auto mt-16">
                <h2 class="font-serif text-2xl text-fg mb-6" style="font-weight:400;">Otros proyectos {{ strtolower($project->status_label) }}s</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($related as $r)
                        <a href="{{ route('projects.show', $r->slug) }}" class="block group">
                            <div class="aspect-[16/10] bg-brand-soft/30 rounded-card overflow-hidden">
                                @if($r->cover_url)
                                    <img src="{{ $r->cover_url }}" alt="{{ $r->title }}" loading="lazy"
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @endif
                            </div>
                            <h3 class="mt-3 font-serif text-lg text-fg leading-tight group-hover:text-brand-primary transition-colors" style="font-weight:400;">
                                {{ $r->title }}
                            </h3>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</article>
@endsection
