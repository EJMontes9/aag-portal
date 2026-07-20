{{-- Cabecera de pagina interior de la Propuesta B.

     Caja BLANCA a todo el ancho, cerrada abajo por el filete amarillo de 3px, y
     dentro: rotulo opcional, titular en Neulis Black navy de 28px EN MAYUSCULAS
     y bajada de 13px en gris.

     El filete amarillo lo aporta ya la propia clase .page-header (que hace
     @apply rule-accent en app.css), por eso no se repite .rule-accent aqui:
     duplicarla no anade nada y hace creer que son dos reglas distintas.

     Slots:
       kicker / title / description  -> como props o como slots
       meta                          -> fila de chips ENCIMA del titulo
                                        (estado de convocatoria, ubicacion...)
       (slot por defecto)            -> lo que haga falta debajo de la bajada --}}
@props([
    'title',
    'kicker'      => null,
    'description' => null,
])

<header {{ $attributes->merge(['class' => 'page-header']) }}>
    {{-- Se sube el padding vertical un escalon (24->28px y 32->40px): la cabecera
         es el primer bloque de la pagina y necesita mas aire para separar el
         titular del filete amarillo y de la miga de pan que la precede. --}}
    <div class="section-wrap !py-7 md:!py-10">
        @isset($meta)
            <div class="mb-3.5 flex flex-wrap items-center gap-2">
                {{ $meta }}
            </div>
        @endisset

        @if($kicker)
            <span class="kicker block">{{ $kicker }}</span>
        @endif

        <h1 class="font-serif text-page-title uppercase text-brand-navy {{ $kicker ? 'mt-2' : '' }}">
            {{ $title }}
        </h1>

        @if($description)
            {{-- La bajada es texto de lectura, no un metadato: sube a 15px y la
                 medida se limita en "ch" (no en max-w-3xl) para que la linea
                 quede en 65-75 caracteres pese a lo estrecha que es la
                 condensada, que con un ancho fijo en px se pasa de largo. --}}
            <p class="mt-3 max-w-[70ch] text-[15px] leading-[1.65] text-muted">
                {{ $description }}
            </p>
        @endif

        {{ $slot }}
    </div>
</header>
