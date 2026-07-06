@props(['block'])
@php
    $limit = max(1, (int) $block->get('limit', 6));
    $source = $block->get('source', 'featured');
    $categoryId = $block->get('category_id');

    $faqs = \Illuminate\Support\Facades\Cache::remember(
        'faq_block_'.$source.'_'.$limit.'_'.($categoryId ?? '0'),
        300,
        function () use ($source, $limit, $categoryId) {
            $q = \App\Models\Faq::active()->with('category')->orderBy('sort_order');
            if ($source === 'featured') {
                $q->where('featured', true);
            } elseif ($source === 'category' && $categoryId) {
                $q->where('category_id', $categoryId);
            }
            return $q->limit($limit)->get();
        }
    );

    if ($faqs->isEmpty()) return;
@endphp

<section class="bg-bg">
    <div class="section-wrap">
        <header class="max-w-2xl mb-10" data-aos="fade-up">
            @if($block->get('kicker'))
                <span class="font-sans text-[11px] tracking-[0.18em] uppercase text-muted font-semibold">{{ $block->get('kicker') }}</span>
            @endif
            @if($block->get('title'))
                <h2 class="font-serif text-section-title text-fg mt-3">{{ $block->get('title') }}</h2>
            @endif
            @if($block->get('subtitle'))
                <p class="mt-3 text-muted leading-[1.65]">{{ $block->get('subtitle') }}</p>
            @endif
        </header>

        <div x-data="{ openIdx: null }" class="max-w-4xl space-y-3">
            @foreach($faqs as $i => $faq)
                <div class="border border-border rounded-card bg-card overflow-hidden transition-colors"
                     :class="openIdx === {{ $i }} ? 'border-brand-primary/40 bg-brand-soft/10' : ''">
                    <button type="button"
                            @click="openIdx = openIdx === {{ $i }} ? null : {{ $i }}"
                            :aria-expanded="openIdx === {{ $i }}"
                            class="w-full flex items-center justify-between gap-4 text-left px-5 py-4 hover:bg-brand-soft/10 transition-colors">
                        <span class="font-medium text-fg leading-snug">{{ $faq->question }}</span>
                        <svg class="w-5 h-5 shrink-0 text-muted transition-transform duration-200"
                             :class="openIdx === {{ $i }} ? 'rotate-180 text-brand-primary' : ''"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div x-show="openIdx === {{ $i }}"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="display: none;">
                        <div class="px-5 pb-5 pt-1 prose prose-sm max-w-none
                                    prose-p:text-fg/85 prose-p:leading-[1.65]
                                    prose-a:text-brand-primary prose-a:no-underline hover:prose-a:underline
                                    prose-strong:text-fg
                                    prose-ul:my-2 prose-li:my-0.5">
                            {!! $faq->answer !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($block->get('show_view_all'))
            <div class="max-w-4xl mt-8">
                <a href="{{ route('faq.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-brand-primary hover:text-brand-navy transition-colors">
                    {{ $block->get('view_all_label', 'Ver todas las preguntas →') }}
                </a>
            </div>
        @endif
    </div>
</section>
