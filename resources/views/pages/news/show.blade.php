@extends('layouts.app', [
    'title'       => $item->meta_title ?: $item->title,
    'description' => $item->meta_description ?: $item->excerpt,
    'ogType'      => 'article',
    'ogImage'     => $item->cover_url,
])

{{-- ══ Open Graph específico de artículo ══════════════════════════════════════ --}}
@push('og-meta')
    <meta property="article:published_time" content="{{ $item->published_at?->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $item->updated_at->toIso8601String() }}">
    @if($item->author)
        <meta property="article:author" content="{{ $item->author->name }}">
    @endif
    @if($item->category)
        <meta property="article:section" content="{{ $item->category->name }}">
    @endif
@endpush

{{-- ══ JSON-LD: NewsArticle (rich results en Google — fecha, autor, imagen) ═══ --}}
@push('json-ld')
@php
    $siteName = settings('site_name', 'Autoridad Aeroportuaria de Guayaquil');
    $articleSchema = [
        '@context'         => 'https://schema.org',
        '@type'            => 'NewsArticle',
        '@id'              => url()->current() . '#article',
        'headline'         => $item->title,
        'description'      => $item->meta_description ?: $item->excerpt,
        'url'              => url()->current(),
        'datePublished'    => $item->published_at?->toIso8601String(),
        'dateModified'     => $item->updated_at->toIso8601String(),
        'inLanguage'       => 'es-EC',
        'isPartOf'         => ['@id' => url('/') . '#website'],
        'author'           => $item->author ? [
            '@type' => 'Person',
            'name'  => $item->author->name,
        ] : [
            '@type' => 'Organization',
            'name'  => $siteName,
            'url'   => url('/'),
        ],
        'publisher' => [
            '@type' => 'Organization',
            '@id'   => url('/') . '#organization',
            'name'  => $siteName,
            'logo'  => setting_asset('site_logo') ? [
                '@type'  => 'ImageObject',
                'url'    => setting_asset('site_logo'),
            ] : null,
        ],
        'image' => $item->cover_url ? [
            '@type'  => 'ImageObject',
            'url'    => $item->cover_url,
            'width'  => 1200,
            'height' => 630,
        ] : null,
        'articleSection' => $item->category?->name,
        'keywords'       => $item->category?->name,
        'wordCount'      => str_word_count(strip_tags((string)$item->content)),
    ];
    // Limpiar nulls
    $articleSchema = array_filter($articleSchema, fn($v) => $v !== null && $v !== '' && $v !== []);
    if (isset($articleSchema['publisher']['logo']) && $articleSchema['publisher']['logo'] === null) {
        unset($articleSchema['publisher']['logo']);
    }
@endphp
<script type="application/ld+json">
{!! json_ld($articleSchema) !!}
</script>
@endpush

{{-- El BreadcrumbList lo emite <x-ui.breadcrumb-bar> a partir de la misma lista
     de items que se pinta en pantalla, así que no se duplica aquí. --}}

@section('content')
<article class="bg-bg">
    {{-- Miga de pan + cabecera de página interior --}}
    <x-ui.breadcrumb-bar :items="array_values(array_filter([
        ['label' => 'Noticias', 'url' => route('news.index')],
        $item->category ? ['label' => $item->category->name, 'url' => route('news.index', ['categoria' => $item->category->slug])] : null,
        ['label' => $item->title, 'url' => null],
    ]))" />

    <x-ui.page-header :title="$item->title" :description="$item->excerpt">
        @if($item->category)
        <x-slot:meta>
            {{-- El color propio de la categoría (definido por el admin) se
                 respeta como color de TEXTO sobre el tinte celeste del chip. --}}
            <span class="pill"
                  @if($item->category->color) style="color: {{ $item->category->color }};" @endif>
                {{ $item->category->name }}
            </span>
        </x-slot:meta>
        @endif

        {{-- Firma del artículo: sube a 13px porque es información que el lector
             consulta de verdad (quién firma, cuándo y cuánto dura), no un
             adorno; sigue por debajo de la bajada para no competir con ella. --}}
        <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-[13px] text-muted">
            @if($item->author)
                <span>Por <strong class="font-semibold text-fg">{{ $item->author->name }}</strong></span>
                <span aria-hidden="true">·</span>
            @endif
            <time datetime="{{ $item->published_at?->toIso8601String() }}">
                {{ $item->published_at?->translatedFormat('d \\d\\e F \\d\\e Y') }}
            </time>
            <span aria-hidden="true">·</span>
            <span class="num-tabular">{{ $item->reading_time }} min de lectura</span>
        </div>
    </x-ui.page-header>

    {{-- Imagen destacada --}}
    @if($item->cover_url)
        <div class="section-wrap !pb-0">
            <figure class="aspect-[16/9] rounded-card overflow-hidden bg-cloud-gradient">
                <img src="{{ $item->cover_url }}"
                     alt="{{ $item->cover_image_alt ?: $item->title }}"
                     loading="eager" fetchpriority="high" decoding="async"
                     class="w-full h-full object-cover">
            </figure>
        </div>
    @endif

    {{-- Cuerpo --}}
    @php
        // Separar bloques de cuerpo vs sidebar
        $bodyBlocks = [];
        $sidebarBlocks = [];
        if ($item->hasContentBlocks()) {
            foreach ($item->content_blocks as $block) {
                $type = $block['type'] ?? null;
                if (! $type) continue;
                if (\App\NewsBlocks\NewsBlockRegistry::isSidebar($type)) {
                    $sidebarBlocks[] = $block;
                } else {
                    $bodyBlocks[] = $block;
                }
            }
        }
        $hasSidebar = ! empty($sidebarBlocks);
    @endphp

    {{-- Medida de línea: se acota el TEXTO (max-w-[72ch] en cada bloque prose),
         no la columna. Capar la columna entera encogía también fotos y galerías
         y, con sidebar, dejaba un hueco muerto de ~400px entre el cuerpo y la
         barra lateral. Así queda el patrón editorial habitual: los medios al
         ancho de la columna y el texto centrado en su medida de lectura. --}}
    <div class="section-wrap">
        <div class="@if($hasSidebar) news-with-sidebar @else max-w-3xl mx-auto @endif">

            {{-- Columna principal: cuerpo --}}
            <div class="space-y-10 min-w-0">
                @foreach($bodyBlocks as $block)
                    @php
                        $blockType = $block['type'] ?? null;
                        $blockData = $block['data'] ?? [];
                        $viewName = $blockType ? \App\NewsBlocks\NewsBlockRegistry::viewFor($blockType) : null;
                    @endphp
                    @if($viewName && view()->exists($viewName))
                        @include($viewName, ['data' => $blockData])
                    @endif
                @endforeach

                @if(! empty($item->content))
                    {{-- Contenido legacy (RichEditor).
                         Mismos valores que news-blocks/text.blade.php: una noticia
                         debe leerse igual venga del editor por bloques o del
                         campo antiguo. Cuerpo 16px / 1.75 y párrafos separados. --}}
                    <div class="prose max-w-[72ch] mx-auto
                                prose-headings:font-serif prose-headings:uppercase prose-headings:text-brand-navy
                                prose-h2:text-[20px] prose-h2:mt-9 prose-h2:mb-3
                                prose-h3:text-[17px] prose-h3:mt-7 prose-h3:mb-2
                                prose-p:text-[16px] prose-p:text-fg prose-p:leading-[1.75] prose-p:my-[1.15em]
                                prose-li:text-[16px] prose-li:leading-[1.7] prose-li:my-1
                                prose-a:text-brand-primary prose-a:no-underline hover:prose-a:underline
                                prose-strong:text-fg prose-strong:font-semibold
                                prose-blockquote:border-brand-accent prose-blockquote:text-fg
                                prose-img:rounded-card">
                        {!! $item->content !!}
                    </div>
                @endif

                {{-- Compartir --}}
                {{-- Barra de compartir: flex-wrap para que a 360px no desborde,
                     12px para que las etiquetas en mayúsculas se lean, y anillo
                     de foco en cada enlace. --}}
                <div class="mt-10 pt-6 border-t border-border flex flex-wrap items-center gap-x-3 gap-y-2 text-[12px] uppercase tracking-[0.07em] font-bold text-muted">
                    <span>Compartir:</span>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($item->title) }}"
                       target="_blank" rel="noopener"
                       class="rounded-pill hover:text-brand-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg" aria-label="Compartir en Twitter">Twitter</a>
                    <span aria-hidden="true">·</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                       target="_blank" rel="noopener"
                       class="rounded-pill hover:text-brand-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg" aria-label="Compartir en Facebook">Facebook</a>
                    <span aria-hidden="true">·</span>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
                       target="_blank" rel="noopener"
                       class="rounded-pill hover:text-brand-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg" aria-label="Compartir en LinkedIn">LinkedIn</a>
                </div>
            </div>

            {{-- Columna sidebar (sticky) --}}
            @if($hasSidebar)
                <aside class="md:sticky md:top-24 md:self-start space-y-6">
                    @foreach($sidebarBlocks as $block)
                        @php
                            $blockType = $block['type'] ?? null;
                            $blockData = $block['data'] ?? [];
                            $viewName = $blockType ? \App\NewsBlocks\NewsBlockRegistry::viewFor($blockType) : null;
                        @endphp
                        @if($viewName && view()->exists($viewName))
                            @include($viewName, ['data' => $blockData])
                        @endif
                    @endforeach
                </aside>
            @endif
        </div>
    </div>

    {{-- Relacionadas --}}
    @if($related->isNotEmpty())
        <section class="bg-card border-t border-border">
            <div class="section-wrap">
                {{-- Mismo patrón de cabecera de bloque que el resto del portal:
                     kicker celeste + título Neulis + filete amarillo separador. --}}
                <header class="mb-7 rule-accent pb-3">
                    <span class="kicker">Seguir leyendo</span>
                    <h2 class="font-serif text-section-title uppercase text-brand-navy mt-2">También te puede interesar</h2>
                </header>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-[18px]">
                    @foreach($related as $item)
                        @include('pages.news.partials.card', ['item' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</article>
@endsection
