@props(['items' => null])
@php
    // Si no se pasan items, autogeneramos desde la URL actual.
    if ($items === null) {
        $segments = collect(request()->segments())->filter()->values();
        $items = [];
        $url = '';
        foreach ($segments as $i => $seg) {
            $url .= '/'.$seg;
            $items[] = [
                'label' => ucfirst(str_replace(['-', '_'], ' ', $seg)),
                'url' => $i === $segments->count() - 1 ? null : $url,
            ];
        }
    }
    if (empty($items)) return;

    // JSON-LD BreadcrumbList: ayuda a Google a mostrar la ruta de migas en los
    // resultados de busqueda en vez de la URL cruda.
    $breadcrumbList = collect($items)->values()->map(fn ($item, $i) => [
        '@type' => 'ListItem',
        'position' => $i + 2, // 1 = Inicio, ya agregado abajo
        'name' => $item['label'],
        'item' => $item['url'] ?? null,
    ])->prepend([
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Inicio',
        'item' => url('/'),
    ])->map(fn ($li) => array_filter($li, fn ($v) => $v !== null))->all();
@endphp

@push('json-ld')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $breadcrumbList,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

<nav aria-label="Migajas de pan" class="text-xs text-muted">
    <ol class="flex flex-wrap items-center gap-2">
        <li>
            <a href="{{ url('/') }}" class="hover:text-fg transition-colors inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                </svg>
                <span class="sr-only sm:not-sr-only">Inicio</span>
            </a>
        </li>
        @foreach($items as $item)
            <li class="flex items-center gap-2" aria-hidden="true">
                <span class="text-border">/</span>
            </li>
            <li>
                @if(!empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-fg transition-colors">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-fg font-medium" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
