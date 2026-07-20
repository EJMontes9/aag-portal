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
    <div class="section-wrap !py-6 md:!py-8">
        @isset($meta)
            <div class="mb-3 flex flex-wrap items-center gap-2">
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
            <p class="mt-2.5 max-w-3xl text-[13px] leading-[1.6] text-muted">
                {{ $description }}
            </p>
        @endif

        {{ $slot }}
    </div>
</header>
