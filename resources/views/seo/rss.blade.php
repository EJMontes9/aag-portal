<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
@php
    $siteName = settings('site_name', 'Autoridad Aeroportuaria de Guayaquil');

    // CDATA solo se rompe con la secuencia literal "]]>" dentro del contenido;
    // se corta en dos bloques CDATA consecutivos para no perder el resto del texto.
    $cdataSafe = function (?string $value): string {
        return str_replace(']]>', ']]]]><![CDATA[>', (string) $value);
    };
@endphp
<rss version="2.0">
    <channel>
        <title><![CDATA[{!! $cdataSafe($siteName . ' — Noticias') !!}]]></title>
        <link>{{ route('news.index') }}</link>
        <description><![CDATA[{!! $cdataSafe('Últimas noticias de ' . $siteName) !!}]]></description>
        <language>es</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>

        @foreach($news as $item)
            @php
                // strip_tags también sobre el excerpt: aunque debería ser texto plano,
                // no hay garantía de que nadie meta markup ahí, y un <tag> sin cerrar
                // en la descripción rompe el render del lector de feeds igual que en XML.
                $description = $item->excerpt ?: $item->content;
                $description = strip_tags($description);
                $description = \Illuminate\Support\Str::limit(trim($description), 300);
            @endphp
            <item>
                <title><![CDATA[{!! $cdataSafe($item->title) !!}]]></title>
                <link>{{ route('news.show', $item->slug) }}</link>
                <description><![CDATA[{!! $cdataSafe($description) !!}]]></description>
                <pubDate>{{ $item->published_at->toRssString() }}</pubDate>
                <guid isPermaLink="true">{{ route('news.show', $item->slug) }}</guid>
            </item>
        @endforeach
    </channel>
</rss>
