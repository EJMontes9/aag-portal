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

{{-- Sección noticias estilo Propuesta B:
     título ALL CAPS Neulis Black navy + "VER TODAS →" en azul a la derecha
     fondo blanco, grid de 4 cards compactas --}}
<section class="bg-card">
    <div class="section-wrap">
        <header class="flex items-center justify-between mb-8" data-aos="fade-up">
            <h2 class="font-serif text-[18px] font-bold text-brand-navy tracking-[0.06em] uppercase">
                {{ $block->get('title', 'NOTICIAS Y BOLETINES') }}
            </h2>
            @if($block->get('show_view_all'))
                <a href="{{ route('news.index') }}"
                   class="text-[11px] font-bold text-brand-primary hover:text-brand-navy tracking-[0.05em] transition-colors uppercase">
                    {{ $block->get('view_all_label', 'VER TODAS →') }}
                </a>
            @endif
        </header>

        <div class="grid sm:grid-cols-2 lg:grid-cols-{{ min($items->count(), 4) }} gap-6">
            @foreach($items as $item)
                @include('pages.news.partials.card', ['item' => $item])
            @endforeach
        </div>
    </div>
</section>
