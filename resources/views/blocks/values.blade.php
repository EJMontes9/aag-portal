@props(['block'])
@php
    $items = $block->get('items', []);
    if (empty($items)) return;

    // Rejilla en clases LITERALES (Tailwind no compila las construidas en runtime).
    // Con 4 o mas valores se usan 4 columnas; con 2 o 3, tantas como haya, para
    // no dejar huecos sueltos al final de la fila.
    $n = count($items);
    $gridClass = match(true) {
        $n === 1 => 'grid-cols-1',
        $n === 2 => 'sm:grid-cols-2',
        $n === 3 => 'sm:grid-cols-2 lg:grid-cols-3',
        default  => 'sm:grid-cols-2 lg:grid-cols-4',
    };
@endphp

{{-- Valores institucionales.

     No existe en la maqueta B, asi que se construye con su vocabulario. Antes
     iba como lista dentro de una unica caja con borde: encerraba el contenido y
     pesaba visualmente. Ahora cada valor es una pieza independiente rematada
     por el filete amarillo de 3px -- el mismo gesto que separa las bandas del
     header y la cabecera de pagina -- con el ordinal en grande como ancla
     visual. Se lee como una rejilla corporativa, no como un formulario. --}}
<section class="bg-bg">
    <div class="section-wrap">

        {{-- Encabezado a lo ancho: da mas aire que la columna estrecha anterior --}}
        <div class="max-w-3xl mb-8" data-aos="fade-up">
            @if($block->get('kicker'))
                <span class="kicker">{{ $block->get('kicker') }}</span>
            @endif
            @if($block->get('title'))
                <h2 class="font-serif text-section-title text-brand-navy mt-2">
                    {!! italic_markdown($block->get('title')) !!}
                </h2>
            @endif
            @if($block->get('subtitle'))
                <p class="mt-3 text-[15px] text-muted leading-relaxed">{{ $block->get('subtitle') }}</p>
            @endif
        </div>

        <div class="grid {{ $gridClass }} gap-4">
            @foreach($items as $v)
                <article class="group relative bg-card border border-border rounded-card p-6 pt-7 transition-colors duration-200 hover:border-brand-primary"
                         data-stagger="value-row" style="opacity:0;">

                    {{-- Filete superior amarillo: al pasar el raton se extiende a
                         todo el ancho de la tarjeta, unico movimiento permitido
                         (es color/tamano de un filete, no un desplazamiento). --}}
                    <span class="absolute top-0 left-0 h-[3px] w-12 bg-brand-accent transition-all duration-300 group-hover:w-full" aria-hidden="true"></span>

                    <span class="block font-serif text-[34px] leading-none text-brand-accent num-tabular">
                        {{ $v['number'] ?? '' }}
                    </span>

                    <h3 class="mt-3 font-sans font-bold text-[15px] text-brand-navy uppercase tracking-[0.05em] leading-snug">
                        {{ $v['title'] ?? '' }}
                    </h3>

                    @if(!empty($v['description']))
                        <p class="mt-2 text-sm text-muted leading-relaxed">{{ $v['description'] }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
