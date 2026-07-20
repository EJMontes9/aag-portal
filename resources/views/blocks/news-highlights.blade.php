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

{{-- Noticias — Propuesta B: titulo en MAYUSCULAS Neulis Black navy a la
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
        <header class="flex items-center justify-between gap-4 mb-6" data-aos="fade-up">
            <h2 class="font-serif text-[18px] text-brand-navy tracking-[0.06em] uppercase">
                {{ $block->get('title', 'NOTICIAS Y BOLETINES') }}
            </h2>
            @if($block->get('show_view_all'))
                <a href="{{ route('news.index') }}"
                   class="shrink-0 text-[11px] font-bold text-brand-primary hover:text-brand-navy tracking-[0.05em] transition-colors uppercase">
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
