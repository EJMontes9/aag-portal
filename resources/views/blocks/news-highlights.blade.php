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

<section class="bg-bg">
    <div class="section-wrap">
        <header class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-12" data-aos="fade-up">
            <div class="max-w-2xl">
                @if($block->get('kicker'))
                    <span class="font-sans text-[11px] tracking-[0.18em] uppercase text-muted font-semibold">{{ $block->get('kicker') }}</span>
                @endif
                @if($block->get('title'))
                    <h2 class="font-serif text-section-title text-fg mt-3">{{ $block->get('title') }}</h2>
                @endif
                @if($block->get('subtitle'))
                    <p class="mt-4 text-muted leading-[1.65]">{{ $block->get('subtitle') }}</p>
                @endif
            </div>
            @if($block->get('show_view_all'))
                <a href="{{ route('news.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-brand-primary hover:text-brand-navy transition-colors">
                    {{ $block->get('view_all_label', 'Ver todas las noticias →') }}
                </a>
            @endif
        </header>

        <div class="grid sm:grid-cols-2 lg:grid-cols-{{ min($items->count(), 4) }} gap-8">
            @foreach($items as $item)
                @include('pages.news.partials.card', ['item' => $item])
            @endforeach
        </div>
    </div>
</section>
