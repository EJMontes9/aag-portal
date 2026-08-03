@props(['block'])
@php
    $limit = (int) $block->get('limit', 3);
    $source = $block->get('source', 'featured');

    $items = \Illuminate\Support\Facades\Cache::remember(
        'news_home_highlights_'.$source.'_'.$limit,
        300, // 5 min
        function () use ($source, $limit) {
            $q = \App\Models\News::published()->with('category', 'author');
            if ($source === 'featured') {
                $q->where('featured_on_home', true);
            }
            return $q->latest('published_at')->limit($limit)->get();
        }
    );

    if ($items->isEmpty()) return;
@endphp

{{-- Noticias — Propuesta B: título en MAYÚSCULAS Neulis Black navy a la
     izquierda y "VER TODAS →" en celeste a la derecha, sobre fondo blanco. --}}
@php
    // Rejilla ESTATICA: una clase construida en runtime ("lg:grid-cols-{$n}")
    // no existe cuando Tailwind escanea las plantillas en build, y por tanto
    // nunca se compila. Hay que escribir las variantes literales.
    $cols = min($items->count(), 4);
    $gridClass = match($cols) {
        1 => 'grid-cols-1',
        2 => 'sm:grid-cols-2',
        3 => 'sm:grid-cols-2 lg:grid-cols-3',
        default => 'sm:grid-cols-2 lg:grid-cols-4',
    };
@endphp
<section class="bg-card">
    <div class="section-wrap">
        {{-- flex-wrap: por debajo de ~380px el título y el "ver todas" no caben
             en la misma línea y se apilan en vez de comprimirse. --}}
        <header class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 mb-8" data-aos="fade-up">
            <h2 class="font-serif text-[18px] text-brand-navy tracking-[0.06em] uppercase">
                {{ $block->get('title', 'NOTICIAS Y BOLETINES') }}
            </h2>
            @if($block->get('show_view_all'))
                {{-- Mismo tratamiento de "ver todas" en todos los bloques: 12px,
                     celeste -> navy en hover y anillo de foco para teclado. --}}
                <a href="{{ route('news.index') }}" wire:navigate
                   class="shrink-0 inline-flex items-center rounded-pill text-[12px] font-bold text-brand-primary hover:text-brand-navy tracking-[0.06em] transition-colors uppercase focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
                    {{ $block->get('view_all_label', 'VER TODAS →') }}
                </a>
            @endif
        </header>

        <div class="grid {{ $gridClass }} gap-4">
            @foreach($items as $item)
                @include('pages.news.partials.card', ['item' => $item])
            @endforeach
        </div>
    </div>
</section>
