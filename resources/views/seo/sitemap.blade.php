<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

    {{-- ══ PÁGINAS DINÁMICAS ════════════════════════════════════════════════ --}}
    @foreach($pages as $page)
        @php
            if ($page->key === 'home') {
                $loc = url('/');
            } elseif ($page->slug) {
                $loc = url('/' . $page->slug);
            } else {
                continue;
            }
            $priority = $page->key === 'home' ? '1.0' : '0.8';
            $changefreq = $page->key === 'home' ? 'daily' : 'weekly';
        @endphp
        <url>
            <loc>{{ $loc }}</loc>
            <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
            <changefreq>{{ $changefreq }}</changefreq>
            <priority>{{ $priority }}</priority>
        </url>
    @endforeach

    {{-- ══ SECCIÓN NOTICIAS ════════════════════════════════════════════════ --}}
    <url>
        <loc>{{ route('news.index') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    @foreach($news as $item)
        <url>
            <loc>{{ route('news.show', $item->slug) }}</loc>
            <lastmod>{{ $item->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
            @if($item->cover_image)
                @php
                    $imgUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($item->cover_image);
                @endphp
                <image:image>
                    <image:loc>{{ $imgUrl }}</image:loc>
                </image:image>
            @endif
        </url>
    @endforeach

    {{-- ══ SECCIÓN FAQ ═════════════════════════════════════════════════════ --}}
    @if($faqs > 0)
    <url>
        <loc>{{ route('faq.index') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endif

    {{-- ══ SECCIÓN PROYECTOS ════════════════════════════════════════════════ --}}
    <url>
        <loc>{{ route('projects.index') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    @foreach($projects as $project)
        <url>
            <loc>{{ route('projects.show', $project->slug) }}</loc>
            <lastmod>{{ $project->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach

</urlset>
