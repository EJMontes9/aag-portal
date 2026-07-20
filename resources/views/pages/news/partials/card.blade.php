{{-- Tarjeta de noticia — ".b-card" de la Propuesta B.

     Caja blanca de borde marcado y esquinas de 4px, SIN sombra: en B la
     elevacion se expresa con el borde. El unico efecto de hover es el cambio
     de color del borde y del titulo; nada de escalados ni desplazamientos.

     El contenedor de imagen lleva el gradiente navy->celeste de fondo, que
     queda a la vista como placeholder cuando la noticia no tiene portada. --}}
<article class="group flex flex-col card-surface overflow-hidden transition-colors duration-200 hover:border-brand-primary">
    <a href="{{ route('news.show', $item->slug) }}" class="block aspect-[16/10] overflow-hidden bg-cloud-gradient">
        @if($item->cover_url)
            <img src="{{ $item->cover_url }}"
                 alt="{{ $item->cover_image_alt ?: $item->title }}"
                 loading="lazy"
                 class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center text-white/40">
                <svg class="w-11 h-11" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 1 1.5 1.5Z"/>
                </svg>
            </div>
        @endif
    </a>
    <div class="p-4 flex flex-col flex-1">
        @if($item->category)
            {{-- Chip rectangular sobre tinte celeste. Si la categoria trae color
                 propio del admin, se respeta como color de texto. --}}
            <span class="pill self-start mb-2"
                  @if($item->category->color) style="color: {{ $item->category->color }};" @endif>
                {{ $item->category->name }}
            </span>
        @endif
        <h3 class="font-sans font-semibold text-sm text-fg leading-snug flex-1">
            <a href="{{ route('news.show', $item->slug) }}" class="transition-colors group-hover:text-brand-primary">
                {{ $item->title }}
            </a>
        </h3>
        <div class="mt-3 text-[11px] text-muted">
            <time datetime="{{ $item->published_at?->toIso8601String() }}">
                {{ $item->published_at?->format('d.m.Y') }}
            </time>
            <span aria-hidden="true"> · </span>
            <span>Por {{ $item->author?->name ?? 'AAG' }}</span>
        </div>
    </div>
</article>
