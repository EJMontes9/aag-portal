{{-- Miga de pan de la Propuesta B.

     A diferencia de <x-layout.breadcrumbs> (que dibuja las migas DENTRO del
     contenido), en B la miga es una BANDA a todo el ancho: fondo gris #f5f5f5,
     filete inferior, 11px en gris y separador tipografico "›". Por eso este
     componente se coloca fuera de .section-wrap y trae su propio contenedor.

     Emite tambien el BreadcrumbList de schema.org, de modo que la pagina que lo
     use NO debe empujar otro a mano: hasta ahora varias vistas hacian ambas
     cosas y Google recibia el mismo breadcrumb duplicado. --}}
@props(['items' => []])

@php
    $items = collect($items)->filter()->values();

    // Posicion 1 siempre es "Inicio"; el resto se numera a continuacion.
    // En el JSON-LD va el label COMPLETO aunque en pantalla se recorte, porque
    // el buscador usa este texto para dibujar la ruta en el SERP.
    $breadcrumbList = $items->map(fn ($item, $i) => array_filter([
        '@type'    => 'ListItem',
        'position' => $i + 2,
        'name'     => $item['label'],
        'item'     => $item['url'] ?? null,
    ], fn ($v) => $v !== null))->prepend([
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => 'Inicio',
        'item'     => url('/'),
    ])->all();
@endphp

@push('json-ld')
<script type="application/ld+json">
{!! json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => $breadcrumbList,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

<nav aria-label="Miga de pan" {{ $attributes->merge(['class' => 'breadcrumb-bar']) }}>
    {{-- El anillo de foco se declara aqui (y no solo :hover) porque la miga es
         el primer grupo de enlaces que recorre quien navega con teclado: sin
         indicador visible se pierde la posicion nada mas entrar a la pagina. --}}
    <div class="section-wrap !py-3">
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <li>
                <a href="{{ url('/') }}"
                   class="rounded-pill transition-colors hover:text-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">Inicio</a>
            </li>
            @foreach($items as $item)
                <li aria-hidden="true" class="text-border">&rsaquo;</li>
                <li class="min-w-0">
                    @if(!empty($item['url']))
                        <a href="{{ $item['url'] }}"
                           class="rounded-pill transition-colors hover:text-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                            {{ \Illuminate\Support\Str::limit($item['label'], 60) }}
                        </a>
                    @else
                        <span class="text-fg font-semibold" aria-current="page">
                            {{ \Illuminate\Support\Str::limit($item['label'], 60) }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</nav>
