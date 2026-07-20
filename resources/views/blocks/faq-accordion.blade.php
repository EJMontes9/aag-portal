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

{{-- Preguntas frecuentes -- Propuesta B (.b-faq).
     El acordeon NO son cajas sueltas apiladas: es UNA caja blanca con borde
     marcado cuyas filas se separan con filete gris. El indicador es el caracter
     "-" / "+", no un chevron SVG rotado (B no tiene iconografia decorativa en
     esta pieza y el signo condensa mejor con Barlow). --}}
<section class="bg-bg">
    <div class="section-wrap">
        <header class="max-w-2xl mb-8" data-aos="fade-up">
            @if($block->get('kicker'))
                <span class="kicker">{{ $block->get('kicker') }}</span>
            @endif
            @if($block->get('title'))
                <h2 class="font-serif text-section-title text-brand-navy mt-2">{{ $block->get('title') }}</h2>
            @endif
            @if($block->get('subtitle'))
                <p class="mt-3 text-[15px] text-muted leading-relaxed">{{ $block->get('subtitle') }}</p>
            @endif
        </header>

        <div x-data="{ openIdx: null }" class="max-w-4xl card-surface overflow-hidden">
            @foreach($faqs as $i => $faq)
                {{-- last:border-0: el borde inferior de la ultima fila lo pone ya
                     la caja contenedora, si no quedaria doble. --}}
                <div class="border-b border-border last:border-0 transition-colors"
                     :class="openIdx === {{ $i }} ? 'bg-brand-soft/40' : ''">
                    {{-- focus-visible con anillo interior (ring-inset): la fila llega
                         al borde de la caja, un anillo por fuera quedaria cortado. --}}
                    <button type="button"
                            @click="openIdx = openIdx === {{ $i }} ? null : {{ $i }}"
                            :aria-expanded="openIdx === {{ $i }}"
                            class="w-full flex items-center justify-between gap-4 text-left px-5 py-4 hover:bg-brand-soft/40 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-primary">
                        <span class="font-sans text-[15px] font-bold text-brand-navy leading-snug">{{ $faq->question }}</span>
                        {{-- aria-hidden: el estado ya lo comunica aria-expanded del boton,
                             el signo es puramente visual. --}}
                        <span class="shrink-0 font-sans text-[18px] font-bold leading-none w-4 text-center transition-colors"
                              :class="openIdx === {{ $i }} ? 'text-brand-primary' : 'text-muted'"
                              x-text="openIdx === {{ $i }} ? '−' : '+'"
                              aria-hidden="true">+</span>
                    </button>
                    <div x-show="openIdx === {{ $i }}"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="display: none;">
                        {{-- La respuesta es texto de lectura corrida: sube a 14px,
                             un escalon por debajo de la pregunta (15px/700) para que
                             la jerarquia se mantenga. --}}
                        <div class="px-5 pb-5 pt-0 prose prose-sm max-w-none
                                    prose-p:text-[14px] prose-p:text-muted prose-p:leading-relaxed prose-p:my-2
                                    prose-li:text-[14px] prose-li:text-muted prose-li:my-1
                                    prose-a:text-brand-primary prose-a:no-underline hover:prose-a:underline
                                    prose-strong:text-brand-navy
                                    prose-ul:my-2">
                            {!! $faq->answer !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($block->get('show_view_all'))
            <div class="max-w-4xl mt-6">
                {{-- Mismo tratamiento de "ver todas" que news-highlights. --}}
                <a href="{{ route('faq.index') }}"
                   class="inline-flex items-center gap-2 rounded-pill text-[12px] font-bold uppercase tracking-[0.06em] text-brand-primary hover:text-brand-navy transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                    {{ $block->get('view_all_label', 'Ver todas las preguntas →') }}
                </a>
            </div>
        @endif
    </div>
</section>
