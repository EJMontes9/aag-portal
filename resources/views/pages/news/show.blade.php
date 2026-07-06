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
{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

{{-- BreadcrumbList --}}
<script type="application/ld+json">
{!! json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => array_values(array_filter([
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio',   'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Noticias', 'item' => route('news.index')],
        $item->category ? ['@type' => 'ListItem', 'position' => 3, 'name' => $item->category->name, 'item' => route('news.index', ['categoria' => $item->category->slug])] : null,
        ['@type' => 'ListItem', 'position' => $item->category ? 4 : 3, 'name' => $item->title],
    ])),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<article class="bg-bg">
    {{-- Hero del articulo --}}
    <header class="border-b border-border">
        <div class="section-wrap pt-12 pb-10">
            <x-layout.breadcrumbs :items="array_filter([
                ['label' => 'Noticias', 'url' => route('news.index')],
                $item->category ? ['label' => $item->category->name, 'url' => route('news.index', ['categoria' => $item->category->slug])] : null,
                ['label' => $item->title, 'url' => null],
            ])" class="mb-6" />

            <div class="max-w-3xl">
                @if($item->category)
                    <span class="font-sans text-[10px] tracking-[0.18em] uppercase font-semibold"
                          style="color: {{ $item->category->color ?: 'rgb(var(--color-primary))' }};">
                        {{ $item->category->name }}
                    </span>
                @endif
                <h1 class="font-serif text-[2.25rem] md:text-[3rem] leading-[1.1] text-fg mt-4" style="font-weight:500;">
                    {{ $item->title }}
                </h1>
                @if($item->excerpt)
                    <p class="mt-6 text-lg text-muted leading-[1.55]">{{ $item->excerpt }}</p>
                @endif
                <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-muted">
                    @if($item->author)
                        <span>Por <strong class="text-fg font-medium">{{ $item->author->name }}</strong></span>
                        <span aria-hidden="true">·</span>
                    @endif
                    <time datetime="{{ $item->published_at?->toIso8601String() }}">
                        {{ $item->published_at?->translatedFormat('d \\d\\e F \\d\\e Y') }}
                    </time>
                    <span aria-hidden="true">·</span>
                    <span>{{ $item->reading_time }} min de lectura</span>
                </div>
            </div>
        </div>
    </header>

    {{-- Imagen destacada --}}
    @if($item->cover_url)
        <div class="section-wrap !pt-10 !pb-0">
            <figure class="aspect-[16/9] rounded-hero overflow-hidden bg-brand-soft/30">
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
                    {{-- Contenido legacy (RichEditor) --}}
                    <div class="prose prose-lg max-w-none
                                prose-headings:font-serif prose-headings:text-fg
                                prose-p:text-fg/85 prose-p:leading-[1.75]
                                prose-a:text-brand-primary prose-a:no-underline hover:prose-a:underline
                                prose-strong:text-fg
                                prose-img:rounded-card">
                        {!! $item->content !!}
                    </div>
                @endif

                {{-- Compartir --}}
                <div class="mt-12 pt-8 border-t border-border flex items-center gap-3 text-sm text-muted">
                    <span>Compartir:</span>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($item->title) }}"
                       target="_blank" rel="noopener"
                       class="hover:text-brand-primary transition-colors" aria-label="Compartir en Twitter">Twitter</a>
                    <span aria-hidden="true">·</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                       target="_blank" rel="noopener"
                       class="hover:text-brand-primary transition-colors" aria-label="Compartir en Facebook">Facebook</a>
                    <span aria-hidden="true">·</span>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
                       target="_blank" rel="noopener"
                       class="hover:text-brand-primary transition-colors" aria-label="Compartir en LinkedIn">LinkedIn</a>
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
        <section class="bg-brand-soft/20 border-t border-border">
            <div class="section-wrap">
                <header class="mb-10">
                    <span class="font-sans text-[11px] tracking-[0.18em] uppercase text-muted font-semibold">SEGUIR LEYENDO</span>
                    <h2 class="font-serif text-section-title text-fg mt-2">Tambien te puede interesar</h2>
                </header>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($related as $item)
                        @include('pages.news.partials.card', ['item' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</article>
@endsection
